<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Helper\SelectFactoryInterface;
use Civi\RemoteTools\Helper\WhereFactoryInterface;
use Civi\RemoteTools\PHPUnit\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoader
 */
final class ProfileEntityLoaderTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ProfileEntityLoader $entityLoader;

  /**
   * @var \Civi\RemoteTools\Helper\SelectFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $selectFactoryMock;

  /**
   * @var \Civi\RemoteTools\Helper\WhereFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $whereFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->selectFactoryMock = $this->createMock(SelectFactoryInterface::class);
    $this->whereFactoryMock = $this->createMock(WhereFactoryInterface::class);
    $this->entityLoader = new ProfileEntityLoader(
      $this->api4Mock,
      $this->selectFactoryMock,
      $this->whereFactoryMock,
    );
  }

  public function testGet(): void {
    $resolvedContactId = 2;

    $profileMock = $this->createMock(RemoteEntityProfileInterface::class);
    $profileMock->method('isCheckApiPermissions')
      ->with($resolvedContactId)
      ->willReturn(FALSE);
    $profileMock->method('getEntityName')->willReturn('Entity');

    $remoteWhere = [
      ['foo', '=', 'bar'],
      ['bar', '=', 'baz'],
    ];

    $actionMock = $this->createPartialApi4ActionMock(
      RemoteGetAction::class,
      'RemoteEntity',
      'get',
      ['getResolvedContactId']
    );
    $actionMock->method('getResolvedContactId')->willReturn($resolvedContactId);
    $actionMock->addSelect('foo', 'baz');
    $actionMock->addOrderBy('foo', 'DESC');
    $actionMock->setWhere($remoteWhere);
    $actionMock->setLimit(10);
    $actionMock->setOffset(4);

    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];
    $remoteFields = [
      'foo' => ['name' => 'foo'],
      'baz' => ['name' => 'baz'],
    ];
    $profileMock->method('getRemoteFields')
      ->with($entityFields, $resolvedContactId)
      ->willReturn($remoteFields);

    $entitySelect = ['foo'];
    $remoteSelect = ['foo', 'baz'];
    $this->selectFactoryMock->method('getSelects')
      ->with(['foo', 'baz'], $entityFields, $remoteFields, static::isType('callable'))
      ->willReturn(['entity' => $entitySelect, 'remote' => $remoteSelect]);

    $profileEntitySelect = ['foo', 'extra'];
    $profileMock->method('getSelectFieldNames')
      ->with($entitySelect, 'get', $remoteSelect, $resolvedContactId)
      ->willReturn($profileEntitySelect);

    $profileFilter = Comparison::new('extra', '!=', 'abc');
    $profileMock->method('getFilter')
      ->with('get', $resolvedContactId)
      ->willReturn($profileFilter);

    $entityWhere = [
      ['foo', '=', 'bar'],
    ];
    $entityWhereWithFilter = [
      ['foo', '=', 'bar'],
      ['extra', '!=', 'abc'],
    ];
    $this->whereFactoryMock->method('getWhere')
      ->with(
        $remoteWhere,
        $entityFields,
        $remoteFields,
        static::isType('callable'),
        static::isType('callable')
      )->willReturn($entityWhere);

    $entityValues = ['foo' => 'a', 'extra' => 'e'];
    $this->api4Mock->method('execute')
      ->willReturnMap([
        [
          'Entity',
          'getFields',
          ['checkPermissions' => FALSE],
          new Result(array_values($entityFields)),
        ],
        [
          'Entity',
          'get',
          [
            'select' => $profileEntitySelect,
            'where' => $entityWhereWithFilter,
            'orderBy' => ['foo' => 'DESC'],
            'limit' => 10,
            'offset' => 4,
            'checkPermissions' => FALSE,
          ],
          new Result([$entityValues]),
        ],
      ]);

    $remoteValues = ['foo' => 'a', 'baz' => 'b'];
    $profileMock->method('convertToRemoteValues')
      ->with($entityValues, $actionMock->getSelect(), $resolvedContactId)
      ->willReturn($remoteValues);

    static::assertSame([$remoteValues], $this->entityLoader->get($profileMock, $actionMock)->getArrayCopy());
  }

}
