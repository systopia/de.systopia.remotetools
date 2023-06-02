<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Helper\SelectFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoader
 */
final class ProfileEntityLoaderTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ProfileEntityLoader $entityLoader;

  /**
   * @var \Civi\RemoteTools\Helper\SelectFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $selectFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->selectFactoryMock = $this->createMock(SelectFactoryInterface::class);
    $this->entityLoader = new ProfileEntityLoader(
      $this->api4Mock,
      $this->selectFactoryMock
    );
  }

  public function testGet(): void {
    $resolvedContactId = 2;

    $profileMock = $this->createMock(RemoteEntityProfileInterface::class);
    $profileMock->method('isCheckApiPermissions')
      ->with($resolvedContactId)
      ->willReturn(FALSE);
    $profileMock->method('getEntityName')->willReturn('Entity');

    $actionMock = $this->createPartialMock(RemoteGetAction::class, [
      'getActionName',
      'getEntityName',
      'getResolvedContactId',
      // Required because otherwise option callbacks would be called that (might) require a complete Civi env.
      'getParamInfo',
    ]);
    $actionMock->method('getActionName')->willReturn('get');
    $actionMock->method('getEntityName')->willReturn('RemoteEntity');
    $actionMock->method('getResolvedContactId')->willReturn($resolvedContactId);
    $actionMock->addSelect('foo', 'baz');
    $actionMock->addOrderBy('foo', 'DESC');
    $actionMock->addWhere('foo', '!=', 'bar');
    $actionMock->setLimit(10);
    $actionMock->setOffset(4);

    $profileMock->method('getFilter')
      ->with('get', $resolvedContactId)
      ->willReturn(Comparison::new('bar', '=', 'foo'));

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
            'where' => [
              ['foo', '!=', 'bar'],
              ['bar', '=', 'foo'],
            ],
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
