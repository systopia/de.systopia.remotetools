<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\QueryApplier;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Helper\SelectFactoryInterface;
use Civi\RemoteTools\Helper\WhereFactoryInterface;

/**
 * @phpstan-type comparisonT array{string, string, 2?: scalar|array<scalar>}
 * Actually this should be: array{string, array<int, comparisonT|compositeConditionT>},
 * so that is not possible.
 * @phpstan-type compositeConditionT array{string, array<int, array<int, mixed>>}
 */
final class ProfileEntityLoader implements ProfileEntityLoaderInterface {

  private Api4Interface $api4;

  private SelectFactoryInterface $selectFactory;

  private WhereFactoryInterface $whereFactory;

  public function __construct(
    Api4Interface $api4,
    SelectFactoryInterface $selectFactory,
    WhereFactoryInterface $whereFactory
  ) {
    $this->api4 = $api4;
    $this->selectFactory = $selectFactory;
    $this->whereFactory = $whereFactory;
  }

  public function get(RemoteEntityProfileInterface $profile, RemoteGetAction $action): Result {
    /** @phpstan-var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($profile->getEntityName(), 'getFields', [
      'checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId()),
    ])->indexBy('name')->getArrayCopy();
    $remoteFields = $profile->getRemoteFields($entityFields, $action->getResolvedContactId());

    $selects = $this->createSelectsForGet($profile, $action, $entityFields, $remoteFields);
    $where = $this->createWhereForGet($profile, $action, $entityFields, $remoteFields);

    $entityOrderBy = [];
    foreach ($action->getOrderBy() as $fieldName => $direction) {
      // @todo: Handle implicit joins
      // @todo: Handle remote only fields
      [$fieldNameWithoutOptionListProperty] = explode(':', $fieldName, 2);
      if (isset($entityFields[$fieldNameWithoutOptionListProperty]) || in_array($fieldName, $selects['entity'], TRUE)) {
        $entityOrderBy[$fieldName] = $direction;
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

    if ($selects['differ']) {
      $queryApplier = QueryApplier::new()->setSelect($selects['remote']);
      // @phpstan-ignore-next-line
      $result->exchangeArray($queryApplier->apply($result->getArrayCopy())->getArrayCopy());
    }

    return $result;
  }

  /**
   * @phpstan-param array<array<string, mixed>> $entityFields
   * @phpstan-param array<array<string, mixed>> $remoteFields
   *
   * @phpstan-return array{entity: array<string>, remote: array<string>, differ: bool}
   *   differ is TRUE if there are extra fields in entity that are not part of
   *   remote.
   */
  private function createSelectsForGet(
    RemoteEntityProfileInterface $profile,
    RemoteGetAction $action,
    array $entityFields,
    array $remoteFields
  ): array {
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

  /**
   * @phpstan-param array<array<string, mixed>> $entityFields
   * @phpstan-param array<array<string, mixed>> $remoteFields
   *
   * @phpstan-return array<comparisonT|compositeConditionT>
   */
  private function createWhereForGet(
    RemoteEntityProfileInterface $profile,
    RemoteGetAction $action,
    array $entityFields,
    array $remoteFields
  ): array {
    $implicitJoinAllowedCallback = fn(string $fieldName, string $joinField)
    => $profile->isImplicitJoinAllowed($fieldName, $joinField, $action->getResolvedContactId());
    $convertRemoteComparisonCallback = fn(Comparison $comparison)
    => $profile->convertRemoteFieldComparison($comparison, $action->getResolvedContactId());

    $where = $this->whereFactory->getWhere(
      $action->getWhere(),
      $entityFields,
      $remoteFields,
      $implicitJoinAllowedCallback,
      $convertRemoteComparisonCallback,
    );

    $filter = $profile->getFilter('get', $action->getResolvedContactId());
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    return $where;
  }

}
