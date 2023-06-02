<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\QueryApplier;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Helper\SelectFactoryInterface;

final class ProfileEntityLoader implements ProfileEntityLoaderInterface {

  private Api4Interface $api4;

  private SelectFactoryInterface $selectFactory;

  public function __construct(Api4Interface $api4, SelectFactoryInterface $selectFactory) {
    $this->api4 = $api4;
    $this->selectFactory = $selectFactory;
  }

  public function get(RemoteEntityProfileInterface $profile, RemoteGetAction $action): Result {
    $selects = $this->createSelectsForGet($profile, $action);

    /*
     * @todo: Ensure where only contains remote fields, or fields returned by
     * getFilter(). Otherwise it would be possible to find out values of not
     * exposed fields. (Via implicit joins even of referenced entities.)
     */
    $where = $action->getWhere();
    $filter = $profile->getFilter('get', $action->getResolvedContactId());
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    $entityFields = $this->api4->execute($profile->getEntityName(), 'getFields', [
      'checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId()),
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

    $result = $this->api4->execute($profile->getEntityName(), 'get', [
      'select' => $selects['entity'],
      'where' => $where,
      'orderBy' => $entityOrderBy,
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
      'checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId()),
    ]);

    /** @phpstan-var array<string, mixed> $entityValues */
    foreach ($result as &$entityValues) {
      $entityValues = $profile->convertToRemoteValues(
        $entityValues,
        $selects['remote'],
        $action->getResolvedContactId()
      );
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
   * @phpstan-return array{entity: array<string>, remote: array<string>, differ: bool}
   *   differ is TRUE if there are extra fields in entity that are not part of
   *   remote.
   *
   * @throws \CRM_Core_Exception
   */
  private function createSelectsForGet(RemoteEntityProfileInterface $profile, RemoteGetAction $action): array {
    /** @phpstan-var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($profile->getEntityName(), 'getFields', [
      'checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId()),
    ])->indexBy('name')->getArrayCopy();
    $remoteFields = $profile->getRemoteFields($entityFields, $action->getResolvedContactId());

    $implicitJoinAllowedCallback = fn(string $fieldName, string $joinField)
    => $profile->isImplicitJoinAllowed($fieldName, $joinField, $action->getResolvedContactId());
    $selects = $this->selectFactory->getSelects(
      $action->getSelect(),
      $entityFields,
      $remoteFields,
      $implicitJoinAllowedCallback,
    );
    $entitySelect = $profile->getSelectFieldNames(
      $selects['entity'],
      'get',
      $selects['remote'],
      $action->getResolvedContactId(),
    );
    if ($selects['entity'] != $entitySelect) {
      $selects['entity'] = $entitySelect;
      $selects['differ'] = TRUE;
    }
    else {
      $selects['differ'] = FALSE;
    }

    return $selects;
  }

}
