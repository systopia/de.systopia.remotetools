<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\EntityProfile\Authorization\GrantResult;
use Civi\RemoteTools\EntityProfile\EntityProfilePermissionDecorator;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityDeleterInterface;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoaderInterface;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\FormSpec\ValidatorInterface;
use Civi\RemoteTools\Form\Validation\ValidationError;
use Civi\RemoteTools\Form\Validation\ValidationResult;
use Civi\RemoteTools\PHPUnit\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\ActionHandler\AbstractProfileEntityActionsHandler
 */
final class AbstractProfileEntityActionsHandlerTest extends TestCase {
  use CreateMockTrait;

  private const RESOLVED_CONTACT_ID = 2;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\RemoteTools\EntityProfile\Helper\ProfileEntityDeleterInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $entityDeleterMock;

  /**
   * @var \Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $entityLoaderMock;

  /**
   * @var \Civi\RemoteTools\ActionHandler\AbstractProfileEntityActionsHandler&\PHPUnit\Framework\MockObject\MockObject
   */
  private AbstractProfileEntityActionsHandler $handler;

  /**
   * @var \Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $profileMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->entityDeleterMock = $this->createMock(ProfileEntityDeleterInterface::class);
    $this->entityLoaderMock = $this->createMock(ProfileEntityLoaderInterface::class);
    $this->profileMock = $this->createMock(RemoteEntityProfileInterface::class);
    $this->handler = $this->getMockForAbstractClass(
    AbstractProfileEntityActionsHandler::class,
      [$this->api4Mock, $this->entityDeleterMock, $this->entityLoaderMock, $this->profileMock],
    );

    $this->profileMock->method('getEntityName')->willReturn('Entity');
    $this->profileMock->method('isCheckApiPermissions')
      ->with(self::RESOLVED_CONTACT_ID)
      ->willReturn(FALSE);
  }

  public function testDelete(): void {
    $actionMock = $this->createMock(RemoteDeleteAction::class);

    $result = new Result();
    $this->entityDeleterMock->method('delete')
      ->with(static::isInstanceOf(EntityProfilePermissionDecorator::class), $actionMock)
      ->willReturn([['id' => 1]]);

    static::assertSame([['id' => 1]], $this->handler->delete($actionMock));
  }

  public function testGet(): void {
    $actionMock = $this->createMock(RemoteGetAction::class);

    $result = new Result();
    $this->entityLoaderMock->method('get')
      ->with(static::isInstanceOf(EntityProfilePermissionDecorator::class), $actionMock)
      ->willReturn($result);

    static::assertSame($result, $this->handler->get($actionMock));
  }

  public function testGetFieldsAction(): void {
    $actionMock = $this->createActionMock(RemoteGetFieldsAction::class);
    $actionMock->setLoadOptions(TRUE);
    $actionMock->addValue('id', 12);

    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];
    $this->api4Mock->method('execute')
      ->with('Entity', 'getFields', [
        'loadOptions' => TRUE,
        'values' => ['id' => 12],
        'checkPermissions' => FALSE,
      ])
      ->willReturn(new Result(array_values($entityFields)));

    $remoteEntityFields = [
      'foo' => ['name' => 'foo'],
      'baz' => ['name' => 'baz'],
    ];
    $this->profileMock->method('getRemoteFields')
      ->with($entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($remoteEntityFields);

    $expectedFieldNames = array_merge(array_keys($remoteEntityFields), ['CAN_delete', 'CAN_update']);
    static::assertSame($expectedFieldNames, $this->handler->getFields($actionMock)->column('name'));
  }

  public function testGetCreateForm(): void {
    $actionMock = $this->createActionMock(RemoteGetCreateFormAction::class);
    $arguments = ['key' => 'value'];
    $actionMock->setArguments($arguments);

    $this->profileMock->method('isCreateGranted')
      ->with($arguments, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newPermitted());
    $this->profileMock->method('getFieldLoadOptionsForFormSpec')->willReturn(TRUE);

    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];
    $this->api4Mock->method('execute')
      ->with('Entity', 'getFields', [
        'loadOptions' => TRUE,
        'values' => [],
        'checkPermissions' => FALSE,
      ])
      ->willReturn(new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $this->profileMock->method('getCreateFormSpec')
      ->with($arguments, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $this->handler->method('convertToGetFormActionResult')
      ->with($formSpec)
      ->willReturn(['form' => 'Test']);

    static::assertSame(['form' => 'Test'], $this->handler->getCreateForm($actionMock));
  }

  public function testGetCreateFormDeiniedWithForm(): void {
    $actionMock = $this->createActionMock(RemoteGetCreateFormAction::class);
    $arguments = ['key' => 'value'];
    $actionMock->setArguments($arguments);

    $this->profileMock->method('isCreateGranted')
      ->with($arguments, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newDeniedWithForm());
    $this->profileMock->method('getFieldLoadOptionsForFormSpec')->willReturn(FALSE);

    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];
    $this->api4Mock->method('execute')
      ->with('Entity', 'getFields', [
        'loadOptions' => FALSE,
        'values' => [],
        'checkPermissions' => FALSE,
      ])
      ->willReturn(new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $this->profileMock->method('getCreateFormSpec')
      ->with($arguments, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $this->handler->method('convertToGetFormActionResult')
      ->with($formSpec)
      ->willReturn(['form' => 'Test']);

    static::assertSame(['form' => 'Test'], $this->handler->getCreateForm($actionMock));
  }

  public function testGetCreateFormDenied(): void {
    $actionMock = $this->createActionMock(RemoteGetCreateFormAction::class);
    $arguments = ['key' => 'value'];
    $actionMock->setArguments($arguments);

    $this->profileMock->method('isCreateGranted')
      ->with($arguments, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newDenied('Denied'));

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Denied');

    static::assertSame(['form' => 'Test'], $this->handler->getCreateForm($actionMock));
  }

  public function testValidateCreateForm(): void {
    $actionMock = $this->createActionMock(RemoteValidateCreateFormAction::class);
    $arguments = ['key' => 'value'];
    $formData = ['property' => 'value'];
    $actionMock->setArguments($arguments);
    $actionMock->setData($formData);

    $this->profileMock->method('isCreateGranted')
      ->with($arguments, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newPermitted());
    $this->profileMock->method('getFieldLoadOptionsForFormSpec')->willReturn(TRUE);

    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];
    $this->api4Mock->method('execute')
      ->with('Entity', 'getFields', [
        'loadOptions' => TRUE,
        'values' => [],
        'checkPermissions' => FALSE,
      ])
      ->willReturn(new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $formSpec->appendValidator(new class implements ValidatorInterface {

      public function validate(array $formData, ?array $currentEntityValues, ?int $contactId): ValidationResult {
        return ValidationResult::new(
          ValidationError::new('field', 'invalid1'),
          ValidationError::new('field', 'invalid2'),
        );
      }

    });
    $this->profileMock->method('getCreateFormSpec')
      ->with($arguments, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    static::assertSame([
      'valid' => FALSE,
      'errors' => [
        'field' => ['invalid1', 'invalid2'],
      ],
    ], $this->handler->validateCreateForm($actionMock));
  }

  public function testGetUpdateForm(): void {
    $actionMock = $this->createActionMock(RemoteGetUpdateFormAction::class);
    $actionMock->setId(12);

    $entityValues = ['foo' => 'f'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->profileMock->method('getSelectFieldNames')
      ->with(['*'], 'update', [], self::RESOLVED_CONTACT_ID)
      ->willReturn(['foo']);
    $this->profileMock->method('isUpdateGranted')
      ->with($entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newPermitted());
    $this->profileMock->method('getFieldLoadOptionsForFormSpec')->willReturn(FALSE);

    $valueMap = [
      [
        'Entity',
        'get',
        [
          'select' => ['foo'],
          'where' => [['id', '=', 12]],
          'checkPermissions' => FALSE,
        ],
        new Result([$entityValues]),
      ],
      [
        'Entity',
        'getFields',
        [
          'loadOptions' => FALSE,
          'values' => ['id' => 12],
          'checkPermissions' => FALSE,
        ],
        new Result(array_values($entityFields)),
      ],
    ];
    $this->api4Mock->method('execute')
      ->willReturnMap($valueMap);

    $formSpec = new FormSpec('Title');
    $this->profileMock->method('getUpdateFormSpec')
      ->with($entityValues, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $this->handler->method('convertToGetFormActionResult')
      ->with($formSpec)
      ->willReturn(['form' => 'Test']);

    static::assertSame(['form' => 'Test'], $this->handler->getUpdateForm($actionMock));
  }

  public function testGetUpdateFormDeniedWithForm(): void {
    $actionMock = $this->createActionMock(RemoteGetUpdateFormAction::class);
    $actionMock->setId(12);

    $entityValues = ['foo' => 'f'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->profileMock->method('getSelectFieldNames')
      ->with(['*'], 'update', [], self::RESOLVED_CONTACT_ID)
      ->willReturn(['foo']);
    $this->profileMock->method('isUpdateGranted')
      ->with($entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newDeniedWithForm());
    $this->profileMock->method('getFieldLoadOptionsForFormSpec')->willReturn(TRUE);

    $valueMap = [
      [
        'Entity',
        'get',
        [
          'select' => ['foo'],
          'where' => [['id', '=', 12]],
          'checkPermissions' => FALSE,
        ],
        new Result([$entityValues]),
      ],
      [
        'Entity',
        'getFields',
        [
          'loadOptions' => TRUE,
          'values' => ['id' => 12],
          'checkPermissions' => FALSE,
        ],
        new Result(array_values($entityFields)),
      ],
    ];
    $this->api4Mock->method('execute')
      ->willReturnMap($valueMap);

    $formSpec = new FormSpec('Title');
    $this->profileMock->method('getUpdateFormSpec')
      ->with($entityValues, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $this->handler->method('convertToGetFormActionResult')
      ->with($formSpec)
      ->willReturn(['form' => 'Test']);

    static::assertSame(['form' => 'Test'], $this->handler->getUpdateForm($actionMock));
  }

  public function testGetUpdateFormDenied(): void {
    $actionMock = $this->createActionMock(RemoteGetUpdateFormAction::class);
    $actionMock->setId(12);

    $entityValues = ['foo' => 'f'];

    $this->profileMock->method('getSelectFieldNames')
      ->with(['*'], 'update', [], self::RESOLVED_CONTACT_ID)
      ->willReturn(['foo']);
    $this->profileMock->method('isUpdateGranted')
      ->with($entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newDenied('Denied'));

    $this->api4Mock->method('execute')
      ->with('Entity', 'get', [
        'select' => ['foo'],
        'where' => [['id', '=', 12]],
        'checkPermissions' => FALSE,
      ])
      ->willReturn(
        new Result([$entityValues]),
      );

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Denied');
    $this->handler->getUpdateForm($actionMock);
  }

  /**
   * Intersection types are not supported by phpstan in template.
   *
   * @template T of \Civi\Api4\Generic\AbstractAction //&\Civi\RemoteTools\Api4\Action\RemoteActionInterface
   *
   * @phpstan-param class-string<T> $className
   *
   * @phpstan-return T&MockObject
   */
  private function createActionMock(string $className): MockObject {
    $actionMock = $this->createPartialApi4ActionMock($className, 'RemoteEntity', 'get', ['getResolvedContactId']);
    // @phpstan-ignore-next-line
    $actionMock->method('getResolvedContactId')->willReturn(self::RESOLVED_CONTACT_ID);

    return $actionMock;
  }

}
