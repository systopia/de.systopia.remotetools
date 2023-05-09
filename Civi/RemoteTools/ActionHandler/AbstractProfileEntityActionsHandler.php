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
use Civi\RemoteTools\Api4\Util\SelectUtil;
use Civi\RemoteTools\EntityProfile\EntityProfilePermissionDecorator;
use Civi\RemoteTools\EntityProfile\FormSpec;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\EntityProfile\ValidationResult;
use CRM_Remotetools_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @see RemoteEntityProfileInterface
 */
abstract class AbstractProfileEntityActionsHandler implements RemoteEntityActionsHandlerInterface {

  protected Api4Interface $api4;

  protected RemoteEntityProfileInterface $profile;

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
    $this->profile = new EntityProfilePermissionDecorator($profile);
    $this->checkApiPermissions = $checkApiPermissions;
  }

  /**
   * @inheritDoc
   * @throws \CRM_Core_Exception
   */
  public function delete(RemoteDeleteAction $action): array {
    Assert::notEq($action->getOffset(), 0, 'Offset is not allowed in delete action');

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
      'select' => array_merge(['*'], $this->profile->getExtraFieldNames()),
      'where' => $where,
      'checkPermissions' => $this->checkApiPermissions,
    ]);

    $deleteCount = 0;
    $result = [];
    /** @phpstan-var array<string, mixed>&array{id: int} $entityValues */
    foreach ($getResult as $entityValues) {
      if ($this->profile->isDeleteAllowed($action->getResolvedContactId(), $entityValues)) {
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
    /** @phpstan-var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'checkPermissions' => $this->checkApiPermissions,
    ])->indexBy('name')->getArrayCopy();
    $entityFieldNames = array_keys($entityFields);
    $remoteFieldNames = array_keys($this->profile->getRemoteFields($entityFields));
    $intersectedFieldNames = array_intersect($entityFieldNames, $remoteFieldNames);

    if (['row_count'] === $action->getSelect()) {
      $select = ['row_count'];
    }
    else {
      // @todo: Allow option transformations
      // https://docs.civicrm.org/dev/en/latest/api/v4/pseudoconstants/#option-transformations

      // @todo: Shall we reduce the selected fields already here for performance reasons?
      // Though if the conversion of entity values to remote values depend on
      // fields not in the action, we would get into trouble...
      $select = array_merge(['*'], $this->profile->getExtraFieldNames());
    }

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

    $internalOrderBy = [];
    $externalOrderBy = [];
    foreach ($action->getOrderBy() as $fieldName => $direction) {
      if (in_array($fieldName, $intersectedFieldNames, TRUE)) {
        $internalOrderBy[$fieldName] = $direction;
      }
      else {
        $externalOrderBy[$fieldName] = $direction;
      }
    }

    $result = $this->api4->execute($this->profile->getEntityName(), 'get', [
      'select' => $select,
      'where' => $where,
      'orderBy' => $internalOrderBy,
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
      'checkPermissions' => $this->checkApiPermissions,
    ]);

    /** @phpstan-var array<string, mixed> $entityValues */
    foreach ($result as &$entityValues) {
      $entityValues = $this->profile->convertToRemoteValues($action->getResolvedContactId(), $entityValues);
    }

    if (['row_count'] !== $select || [] !== $externalOrderBy) {
      $queryApplier = QueryApplier::new()
        ->setSelect(array_intersect($action->getSelect(), $remoteFieldNames))
        ->setOrderBy($externalOrderBy);
      // @phpstan-ignore-next-line
      $result->exchangeArray($queryApplier->apply($result->getArrayCopy())->getArrayCopy());
    }

    return $result;
  }

  public function getFields(RemoteGetFieldsAction $action): Result {
    /** @var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($this->profile->getEntityName(), 'getFields', [
      'select' => SelectUtil::ensureFieldSelected('name', $action->getSelect()),
      'where' => $action->getWhere(),
      'orderBy' => $action->getOrderBy(),
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
      'values' => $action->getValues(),
      'loadOptions' => $action->getLoadOptions(),
      'checkPermissions' => $this->checkApiPermissions,
    ])->indexBy('name')->getArrayCopy();

    $remoteFields = $this->profile->getRemoteFields($entityFields);
    if (array_keys($entityFields) != array_keys($remoteFields)) {
      return QueryApplier::new()
        ->setOrderBy($action->getOrderBy())
        ->setSelect($action->getSelect())
        ->setWhere($action->getWhere())
        ->apply($remoteFields);
    }

    if (!SelectUtil::isFieldSelected('name', $action->getSelect())) {
      return QueryApplier::new()->setSelect($action->getSelect())->apply($remoteFields);
    }

    return new Result($remoteFields);
  }

  /**
   * @inheritDoc
   */
  public function getCreateForm(RemoteGetCreateFormAction $action): array {
    if (!$this->profile->isCreateAllowed($action->getResolvedContactId(), $action->getArguments())) {
      throw new UnauthorizedException(E::ts('Permission to create entity is missing'));
    }

    return $this->convertToForm($this->profile->getCreateFormSpec(
      $action->getArguments(),
      $this->getEntityFieldsForFormSpec(),
    ));
  }

  /**
   * @inheritDoc
   */
  public function getUpdateForm(RemoteGetUpdateFormAction $action): array {
    $entityValues = $this->getEntityById($action->getResolvedContactId(), $action->getId());
    if (NULL === $entityValues || !$this->profile->isUpdateAllowed($action->getResolvedContactId(), $entityValues)) {
      throw new UnauthorizedException(E::ts('Permission to update entity is missing'));
    }

    return $this->convertToForm($this->profile->getUpdateFormSpec(
      $entityValues,
      $this->getEntityFieldsForFormSpec(),
    ));
  }

  /**
   * @inheritDoc
   */
  public function validateCreateForm(RemoteValidateCreateFormAction $action
  ): array {
    if (!$this->profile->isCreateAllowed($action->getResolvedContactId(), $action->getArguments())) {
      throw new UnauthorizedException(E::ts('Permission to create entity is missing'));
    }

    $formSpec = $this->profile->getCreateFormSpec(
      $action->getArguments(),
      $this->getEntityFieldsForFormSpec(),
    );
    $validationResult = $this->validateFormData($formSpec, $action->getData());

    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
    }

    $validationResult = $this->profile->validateCreateData($action->getData());
    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
    }

    return [];
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateForm(RemoteValidateUpdateFormAction $action
  ): array {
    $entityValues = $this->getEntityById($action->getResolvedContactId(), $action->getId());
    if (NULL === $entityValues || !$this->profile->isUpdateAllowed($action->getResolvedContactId(), $entityValues)) {
      throw new UnauthorizedException(E::ts('Permission to update entity is missing'));
    }

    $formSpec = $this->profile->getUpdateFormSpec(
      $entityValues,
      $this->getEntityFieldsForFormSpec(),
    );
    $validationResult = $this->validateFormData($formSpec, $action->getData());
    if (!$validationResult->isValid()) {
      return $this->convertToFormErrors($validationResult);
    }

    $validationResult = $this->profile->validateUpdateData($action->getData(), $entityValues);
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
      $this->profile->convertUpdateDataToEntityValues($action->getData()),
      ['checkPermissions' => $this->checkApiPermissions],
    )->first();

    return $this->profile->convertToRemoteValues($updatedValues);
  }

  /**
   * @phpstan-return array<string, mixed>|null
   *
   * @throws \CRM_Core_Exception
   */
  protected function getEntityById(?int $contactId, int $id): ?array {
    $where = [['id', '=', $id]];
    $filter = $this->profile->getFilter($contactId);
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    return $this->api4->execute($this->profile->getEntityName(), 'get', [
      'select' => array_merge(['*'], $this->profile->getExtraFieldNames()),
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

}
