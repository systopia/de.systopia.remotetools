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
use Civi\RemoteTools\EntityProfile\FormSpec;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\EntityProfile\ValidationResult;
use Civi\RemoteTools\Helper\SelectFactory;
use Civi\RemoteTools\Helper\SelectFactoryInterface;
use CRM_Remotetools_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @see RemoteEntityProfileInterface
 */
abstract class AbstractProfileEntityActionsHandler implements RemoteEntityActionsHandlerInterface {

  protected Api4Interface $api4;

  protected RemoteEntityProfileInterface $profile;

  protected SelectFactoryInterface $selectFactory;

  protected bool $checkApiPermissions;

  /**
   * @param bool $checkApiPermissions
   *   Check permissions on CiviCRM API calls. The check will be applied to the
   *   API user not to the resolved remote contact. For this reason FALSE is
   *   used as default, so the API user just needs permission to access the
   *   remote API.
   */
  public function __construct(
    Api4Interface $api4,
    RemoteEntityProfileInterface $profile,
    bool $checkApiPermissions = FALSE
  ) {
    $this->api4 = $api4;
    $this->profile = new EntityProfilePermissionDecorator(new EntityProfileOptionSuffixDecorator($profile));
    $this->checkApiPermissions = $checkApiPermissions;
    // Can be initialized by subclasses.
    // @phpstan-ignore-next-line
    $this->selectFactory ??= new SelectFactory();
  }

  /**
   * @inheritDoc
   * @throws \CRM_Core_Exception
   */
  public function delete(RemoteDeleteAction $action): array {
    Assert::eq($action->getOffset(), 0, 'Offset is not allowed in delete action');

    /*
     * @todo: Ensure where only contains remote fields.
     * Otherwise it would be possible to find out values of not exposed fields.
     * (Via implicit joins even of referenced entities.)
     */
    $where = $action->getWhere();
    $filter = $this->profile->getFilter($action->getResolvedContactId());
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    $getResult = $this->api4->execute($this->profile->getEntityName(), 'get', [
      'select' => $this->profile->getSelectFieldNames(['*'], 'delete', [], $action->getResolvedContactId()),
      'where' => $where,
      'checkPermissions' => $this->checkApiPermissions,
    ]);

    $deleteCount = 0;
    $result = [];
    /** @phpstan-var array<string, mixed>&array{id: int} $entityValues */
    foreach ($getResult as $entityValues) {
      if ($this->profile->isDeleteAllowed($entityValues, $action->getResolvedContactId())) {
        $result = array_merge(
          $result,
          $this->api4->deleteEntity(
            $this->profile->getEntityName(),
            $entityValues['id'],
            ['checkPermissions' => $this->checkApiPermissions]
          )->getArrayCopy(),
        );
        ++$deleteCount;
        if ($deleteCount === $action->getLimit()) {
          break;
        }
      }
    }

    /** @phpstan-var array<array<string, mixed>> $result */
    return $result;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(RemoteGetAction $action): Result {
    $selects = $this->createSelectsForGet($action);

    /*
     * @todo: Ensure where only contains remote fields.
     * Otherwise it would be possible to find out values of not exposed fields.
     * (Via implicit joins even of referenced entities.)
     */
    $where = $action->getWhere();
    $filter = $this->profile->getFilter($action->getResolvedContactId());
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    $entityFields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'checkPermissions' => $this->checkApiPermissions,
    ])->indexBy('name')->getArrayCopy();

    $entityOrderBy = [];
    $orderByRemoteValuesRequired = FALSE;
    foreach ($action->getOrderBy() as $fieldName => $direction) {
      // @todo: Handle implicit joins
      [$fieldNameWithoutOptionListProperty] = explode(':', $fieldName, 2);
      if (isset($entityFields[$fieldNameWithoutOptionListProperty]) || in_array($fieldName, $selects['entity'], TRUE)) {
        $entityOrderBy[$fieldName] = $direction;
      }
      else {
        $orderByRemoteValuesRequired = TRUE;
      }
    }

    $result = $this->api4->execute($this->profile->getEntityName(), 'get', [
      'select' => $selects['entity'],
      'where' => $where,
      'orderBy' => $entityOrderBy,
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
      'checkPermissions' => $this->checkApiPermissions,
    ]);

    /** @phpstan-var array<string, mixed> $entityValues */
    foreach ($result as &$entityValues) {
      $entityValues = $this->profile->convertToRemoteValues($entityValues, $action->getResolvedContactId());
    }

    if ($selects['differ'] || $orderByRemoteValuesRequired) {
      $queryApplier = QueryApplier::new()
        ->setSelect($selects['differ'] ? $selects['remote'] : [])
        ->setOrderBy($action->getOrderBy());
      // @phpstan-ignore-next-line
      $result->exchangeArray($queryApplier->apply($result->getArrayCopy())->getArrayCopy());
    }

    return $result;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFields(RemoteGetFieldsAction $action): Result {
    /** @var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'values' => $action->getValues(),
      'loadOptions' => $action->getLoadOptions(),
      'checkPermissions' => $this->checkApiPermissions,
    ])->indexBy('name')->getArrayCopy();

    $remoteFields = $this->profile->getRemoteFields($entityFields);

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
    if (!$this->profile->isCreateAllowed($action->getArguments(), $action->getResolvedContactId())) {
      throw new UnauthorizedException(E::ts('Permission to create entity is missing'));
    }

    return $this->convertToForm($this->profile->getCreateFormSpec(
      $action->getArguments(),
      $this->getEntityFieldsForFormSpec(),
      $action->getResolvedContactId(),
    ));
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function getUpdateForm(RemoteGetUpdateFormAction $action): array {
    $entityValues = $this->getEntityById($action->getResolvedContactId(), $action->getId(), 'update');
    if (NULL === $entityValues || !$this->profile->isUpdateAllowed($entityValues, $action->getResolvedContactId())) {
      throw new UnauthorizedException(E::ts('Permission to update entity is missing'));
    }

    return $this->convertToForm($this->profile->getUpdateFormSpec(
      $entityValues,
      $this->getEntityFieldsForFormSpec(),
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
    if (!$this->profile->isCreateAllowed($action->getArguments(), $action->getResolvedContactId())) {
      throw new UnauthorizedException(E::ts('Permission to create entity is missing'));
    }

    $formSpec = $this->profile->getCreateFormSpec(
      $action->getArguments(),
      $this->getEntityFieldsForFormSpec(),
      $action->getResolvedContactId(),
    );
    $validationResult = $this->validateFormData($formSpec, $action->getData());

    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
    }

    $validationResult = $this->profile->validateCreateData($action->getData(), $action->getResolvedContactId());
    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
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
    $entityValues = $this->getEntityById($action->getResolvedContactId(), $action->getId(), 'update');
    if (NULL === $entityValues || !$this->profile->isUpdateAllowed($entityValues, $action->getResolvedContactId())) {
      throw new UnauthorizedException(E::ts('Permission to update entity is missing'));
    }

    $formSpec = $this->profile->getUpdateFormSpec(
      $entityValues,
      $this->getEntityFieldsForFormSpec(),
      $action->getResolvedContactId(),
    );
    $validationResult = $this->validateFormData($formSpec, $action->getData());
    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
    }

    $validationResult = $this->profile->validateUpdateData(
      $action->getData(),
      $entityValues,
      $action->getResolvedContactId(),
    );
    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
    }

    return [];
  }

  /**
   * @inheritDoc
   */
  public function submitCreateForm(RemoteSubmitCreateFormAction $action
  ): array {
    // TODO: Implement submitCreateForm() method.
    return [];
  }

  /**
   * @inheritDoc
   */
  public function submitUpdateForm(RemoteSubmitUpdateFormAction $action
  ): array {
    // TODO: Implement submitUpdateForm() method.
    return [];

    // After successful validation:
    $updatedValues = $this->api4->updateEntity(
      $this->profile->getEntityName(),
      $action->getId(),
      $this->profile->convertUpdateDataToEntityValues($action->getData(), $action->getResolvedContactId()),
      ['checkPermissions' => $this->checkApiPermissions],
    )->first();

    return $this->profile->convertToRemoteValues($updatedValues, $action->getContactId());
  }

  /**
   * @phpstan-param 'delete'|'update' $action
   *
   * @phpstan-return array<string, mixed>|null
   *
   * @throws \CRM_Core_Exception
   */
  protected function getEntityById(?int $contactId, int $id, string $action): ?array {
    $where = [['id', '=', $id]];
    $filter = $this->profile->getFilter($contactId);
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    return $this->api4->execute($this->profile->getEntityName(), 'get', [
      'select' => $this->profile->getSelectFieldNames(['*'], $action, [], $contactId),
      'where' => $where,
      'checkPermissions' => $this->checkApiPermissions,
    ])->first();
  }

  /**
   * @phpstan-return array<string, array<string, mixed>>
   *
   * @throws \CRM_Core_Exception
   */
  protected function getEntityFieldsForFormSpec(): array {
    /** @phpstan-var array<string, array<string, mixed>> $fields */
    $fields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'loadOptions' => $this->profile->isFormSpecNeedsFieldOptions(),
      'checkPermissions' => $this->checkApiPermissions,
    ])->indexBy('name')->getArrayCopy();

    return $fields;
  }

  /**
   * @phpstan-return array<int|string, mixed> JSON serializable.
   */
  abstract protected function convertToForm(FormSpec $formSpec): array;

  /**
   * @phpstan-return array<int|string, mixed> JSON serializable.
   */
  abstract protected function convertToFormErrors(ValidationResult $validationResult): array;

  /**
   * @phpstan-param array<string, mixed> $formData JSON serializable.
   */
  abstract protected function validateFormData(FormSpec $formSpec, array $formData): ValidationResult;

  /**
   * @phpstan-return array{entity: array<string>, remote: array<string>, differ: bool}
   *
   * @throws \CRM_Core_Exception
   */
  private function createSelectsForGet(RemoteGetAction $action): array {
    /** @phpstan-var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'checkPermissions' => $this->checkApiPermissions,
    ])->indexBy('name')->getArrayCopy();
    $remoteFields = $this->profile->getRemoteFields($entityFields);

    $implicitJoinAllowedCallback = fn(string $fieldName, string $joinField)
    => $this->profile->isImplicitJoinAllowed($fieldName, $joinField, $action->getResolvedContactId());
    $selects = $this->selectFactory->getSelects(
      $action->getSelect(),
      $entityFields,
      $remoteFields,
      $implicitJoinAllowedCallback,
    );
    $entitySelect = $this->profile->getSelectFieldNames(
      $selects['entity'],
      'get',
      $selects['remote'],
      $action->getResolvedContactId(),
    );
    if ($selects['entity'] != $entitySelect) {
      $selects['entity'] = $entitySelect;
      $selects['differ'] = TRUE;
    }

    return $selects;
  }

}
