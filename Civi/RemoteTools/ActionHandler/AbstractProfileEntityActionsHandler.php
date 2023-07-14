<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\QueryApplier;
use Civi\RemoteTools\EntityProfile\EntityProfileOptionSuffixDecorator;
use Civi\RemoteTools\EntityProfile\EntityProfilePermissionDecorator;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityDeleterInterface;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoaderInterface;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\Validation\ValidationResult;
use CRM_Remotetools_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @see RemoteEntityProfileInterface
 */
abstract class AbstractProfileEntityActionsHandler implements RemoteEntityActionsHandlerInterface {

  protected Api4Interface $api4;

  protected ProfileEntityDeleterInterface $entityDeleter;

  protected ProfileEntityLoaderInterface $entityLoader;

  protected RemoteEntityProfileInterface $profile;

  public function __construct(
    Api4Interface $api4,
    ProfileEntityDeleterInterface $entityDeleter,
    ProfileEntityLoaderInterface $entityLoader,
    RemoteEntityProfileInterface $profile
  ) {
    $this->api4 = $api4;
    $this->entityDeleter = $entityDeleter;
    $this->entityLoader = $entityLoader;
    $this->profile = new EntityProfilePermissionDecorator(
      new EntityProfileOptionSuffixDecorator($profile, $api4)
    );
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function delete(RemoteDeleteAction $action): array {
    return $this->entityDeleter->delete($this->profile, $action);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(RemoteGetAction $action): Result {
    return $this->entityLoader->get($this->profile, $action);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFields(RemoteGetFieldsAction $action): Result {
    /** @var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'values' => $action->getValues(),
      'loadOptions' => $action->getLoadOptions(),
      'checkPermissions' => $this->profile->isCheckApiPermissions($action->getResolvedContactId()),
    ])->indexBy('name')->getArrayCopy();

    $remoteFields = $this->profile->getRemoteFields($entityFields, $action->getResolvedContactId());

    return QueryApplier::new()
      ->setLimit($action->getLimit())
      ->setOffset($action->getOffset())
      ->setOrderBy($action->getOrderBy())
      ->setSelect($action->getSelect())
      ->setWhere($action->getWhere())
      ->apply($remoteFields);
  }

  /**
   * @inheritDoc
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function getCreateForm(RemoteGetCreateFormAction $action): array {
    $grantResult = $this->profile->isCreateGranted($action->getArguments(), $action->getResolvedContactId());
    if (!$grantResult->granted) {
      throw new UnauthorizedException($grantResult->message ?? E::ts('Permission to create entity is missing'));
    }

    return $this->convertToGetFormResult($this->profile->getCreateFormSpec(
      $action->getArguments(),
      $this->getEntityFieldsForFormSpec($action->getResolvedContactId()),
      $action->getResolvedContactId(),
    ));
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function getUpdateForm(RemoteGetUpdateFormAction $action): array {
    $entityValues = $this->getEntityById($action->getId(), 'update', $action->getResolvedContactId());
    $grantResult = $this->profile->isUpdateGranted($entityValues, $action->getResolvedContactId());
    if (NULL === $entityValues || !$grantResult->granted) {
      throw new UnauthorizedException($grantResult->message ?? E::ts('Permission to update entity is missing'));
    }

    return $this->convertToGetFormResult($this->profile->getUpdateFormSpec(
      $entityValues,
      $this->getEntityFieldsForFormSpec($action->getResolvedContactId(), ['id' => $action->getId()]),
      $action->getResolvedContactId(),
    ));
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function validateCreateForm(RemoteValidateCreateFormAction $action
  ): array {
    $validationResult = $this->validateForCreate(
      $action->getData(),
      $action->getArguments(),
      $action->getResolvedContactId(),
    );
    if (!$validationResult->isValid()) {
      return $this->convertToValidateActionResult($validationResult);
    }

    return [];
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function validateUpdateForm(RemoteValidateUpdateFormAction $action
  ): array {
    $validationResult = $this->validateForUpdate($action->getId(), $action->getData(), $action->getResolvedContactId());

    return $this->convertToValidateActionResult($validationResult);
  }

  /**
   * @inheritDoc
   */
  public function submitCreateForm(RemoteSubmitCreateFormAction $action
  ): array {
    $validationResult = $this->validateForCreate(
      $action->getData(),
      $action->getArguments(),
      $action->getResolvedContactId(),
    );
    if (!$validationResult->isValid()) {
      throw $validationResult->toException();
    }

    $createdValues = $this->api4->createEntity(
      $this->profile->getEntityName(),
      $this->profile->convertCreateDataToEntityValues(
        $action->getData(),
        $action->getArguments(),
        $action->getResolvedContactId()
      ),
      ['checkPermissions' => $this->profile->isCheckApiPermissions($action->getResolvedContactId())],
    )->single();

    return $this->convertToSubmitFormResult(
      $createdValues,
      [],
      'create',
      $action->getData(),
      $action->getResolvedContactId()
    );
  }

  /**
   * @inheritDoc
   */
  public function submitUpdateForm(RemoteSubmitUpdateFormAction $action
  ): array {
    $validationResult = $this->validateForUpdate($action->getId(), $action->getData(), $action->getResolvedContactId());
    if (!$validationResult->isValid()) {
      throw $validationResult->toException();
    }

    $entityValues = $this->getEntityById($action->getId(), 'update', $action->getResolvedContactId());
    Assert::notNull($entityValues);
    $newEntityValues = $this->profile->convertUpdateDataToEntityValues(
      $action->getData(),
      $entityValues,
      $action->getResolvedContactId()
    );
    $updatedValues = $this->api4->updateEntity(
      $this->profile->getEntityName(),
      $action->getId(),
      $newEntityValues,
      ['checkPermissions' => $this->profile->isCheckApiPermissions($action->getResolvedContactId())],
    )->single();
    // For values that are not part of $newEntityValues we use the previous
    // ones. Fields updated by triggers might be outdated, though.
    $updatedValues += $entityValues;

    return $this->convertToSubmitFormResult(
      $updatedValues,
      $entityValues,
      'update',
      $action->getData(),
      $action->getResolvedContactId()
    );
  }

  /**
   * @phpstan-param 'delete'|'update' $actionName
   *
   * @phpstan-return array<string, mixed>|null
   *
   * @throws \CRM_Core_Exception
   */
  protected function getEntityById(int $id, string $actionName, ?int $contactId): ?array {
    $where = [['id', '=', $id]];
    $filter = $this->profile->getFilter($actionName, $contactId);
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    return $this->api4->execute($this->profile->getEntityName(), 'get', [
      'select' => $this->profile->getSelectFieldNames(['*'], $actionName, [], $contactId),
      'where' => $where,
      'checkPermissions' => $this->profile->isCheckApiPermissions($contactId),
    ])->first();
  }

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, array<string, mixed>>
   *
   * @throws \CRM_Core_Exception
   */
  protected function getEntityFieldsForFormSpec(?int $contactId, array $values = []): array {
    /** @phpstan-var array<string, array<string, mixed>> $fields */
    $fields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'loadOptions' => $this->profile->isFormSpecNeedsFieldOptions(),
      'values' => $values,
      'checkPermissions' => $this->profile->isCheckApiPermissions($contactId),
    ])->indexBy('name')->getArrayCopy();

    return $fields;
  }

  /**
   * @phpstan-return array<int|string, mixed> JSON serializable.
   */
  abstract protected function convertToGetFormResult(FormSpec $formSpec): array;

  /**
   * @phpstan-return array<int|string|null, mixed> JSON serializable.
   */
  protected function convertToValidateActionResult(ValidationResult $validationResult): array {
    return [
      'valid' => $validationResult->isValid(),
      'errors' => $validationResult->getErrorMessages(),
    ];
  }

  /**
   * @phpstan-param array<string, mixed> $formData
   * @phpstan-param array<int|string, mixed> $arguments
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  private function validateForCreate(array $formData, array $arguments, ?int $contactId): ValidationResult {
    $grantResult = $this->profile->isCreateGranted($arguments, $contactId);
    if (!$grantResult->granted) {
      throw new UnauthorizedException($grantResult->message ?? E::ts('Permission to create entity is missing'));
    }

    $entityFields = $this->getEntityFieldsForFormSpec($contactId);
    $formSpec = $this->profile->getCreateFormSpec($arguments, $entityFields, $contactId);
    $validationResult = $this->validateFormData($formSpec, $formData);

    if (!$validationResult->isValid()) {
      return $validationResult;
    }

    return $this->profile->validateCreateData($formData, $arguments, $entityFields, $contactId);
  }

  /**
   * @phpstan-param array<string, mixed> $formData JSON serializable.
   */
  abstract protected function validateFormData(FormSpec $formSpec, array $formData): ValidationResult;

  /**
   * @phpstan-param array<string, mixed> $formData
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  private function validateForUpdate(int $id, array $formData, ?int $contactId): ValidationResult {
    $entityValues = $this->getEntityById($id, 'update', $contactId);
    $grantResult = $this->profile->isUpdateGranted($entityValues, $contactId);
    if (NULL === $entityValues || !$grantResult->granted) {
      throw new UnauthorizedException($grantResult->message ?? E::ts('Permission to update entity is missing'));
    }

    $entityFields = $this->getEntityFieldsForFormSpec($contactId, ['id' => $id]);
    $formSpec = $this->profile->getUpdateFormSpec($entityValues, $entityFields, $contactId);

    $validationResult = $this->validateFormData($formSpec, $formData);
    if (!$validationResult->isValid()) {
      return $validationResult;
    }

    return $this->profile->validateUpdateData($formData, $entityValues, $entityFields, $contactId);
  }

  /**
   * @phpstan-param array<string, mixed> $newValues
   * @phpstan-param array<string, mixed> $oldValues
   *   Empty array on create.
   * @phpstan-param 'create'|'update' $action
   * @phpstan-param array<string, mixed> $formData
   *
   * @phpstan-return array<int|string, mixed> JSON serializable.
   */
  protected function convertToSubmitFormResult(
    array $newValues,
    array $oldValues,
    string $action,
    array $formData,
    ?int $contactId
  ): array {
    $message = $this->profile->getSaveSuccessMessage($newValues, $oldValues, $action, $formData, $contactId);

    return [
      'message' => $message,
    ];
  }

}
