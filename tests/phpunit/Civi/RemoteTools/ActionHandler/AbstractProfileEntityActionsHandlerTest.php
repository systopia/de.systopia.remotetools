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
use Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\Join;
use Civi\RemoteTools\EntityProfile\Authorization\GrantResult;
use Civi\RemoteTools\EntityProfile\EntityProfilePermissionDecorator;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityDeleterInterface;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoaderInterface;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Form\FormSpec\DataTransformerInterface;
use Civi\RemoteTools\Form\FormSpec\Field\TextField;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\FormSpec\ValidatorInterface;
use Civi\RemoteTools\Form\Validation\ValidationError;
use Civi\RemoteTools\Form\Validation\ValidationResult;
use Civi\RemoteTools\PHPUnit\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use PHPUnit\Framework\MockObject\Stub\Stub;
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

  private bool $fieldLoadOptionsForFormSpec = FALSE;

  /**
   * @var \Civi\RemoteTools\ActionHandler\AbstractProfileEntityActionsHandler&\PHPUnit\Framework\MockObject\MockObject
   */
  private AbstractProfileEntityActionsHandler $handler;

  /**
   * @var \Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $profileMock;

  private ?Stub $api4ExecuteStub = NULL;

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
    $this->profileMock->method('getFieldLoadOptionsForFormSpec')
      ->willReturnCallback(fn() => $this->fieldLoadOptionsForFormSpec);
  }

  public function testDelete(): void {
    $actionMock = $this->createMock(RemoteDeleteAction::class);

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

    $this->mockIsCreateGranted($arguments, GrantResult::newPermitted());
    $this->fieldLoadOptionsForFormSpec = TRUE;

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

  public function testGetCreateFormDeniedWithForm(): void {
    $actionMock = $this->createActionMock(RemoteGetCreateFormAction::class);
    $arguments = ['key' => 'value'];
    $actionMock->setArguments($arguments);

    $this->mockIsCreateGranted($arguments, GrantResult::newDeniedWithForm());

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

    $this->mockIsCreateGranted($arguments, GrantResult::newDenied('Denied'));

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

    $this->mockIsCreateGranted($arguments, GrantResult::newPermitted());
    $this->fieldLoadOptionsForFormSpec = TRUE;

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

  public function testSubmitCreateForm(): void {
    $actionMock = $this->createActionMock(RemoteSubmitCreateFormAction::class);
    $arguments = ['key' => 'value'];
    $actionMock->setArguments(['key' => 'value']);
    $formData = ['foo' => 'bar', 'bar' => 'baz'];
    $actionMock->setData($formData);

    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->mockIsCreateGranted($arguments, GrantResult::newPermitted());

    $this->mockApi4Execute('Entity', 'getFields', [
      'loadOptions' => FALSE,
      'values' => [],
      'checkPermissions' => FALSE,
    ], new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $formSpec->addElement(new TextField('foo', 'Foo'));
    $formSpec->addElement((new TextField('bar', 'Bar'))->setReadOnly(TRUE));

    $this->profileMock->method('getCreateFormSpec')
      ->with($arguments, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $validatorMock = $this->createMock(ValidatorInterface::class);
    $formSpec->appendValidator($validatorMock);
    $validatorMock->expects(static::once())->method('validate')
      ->with($formData, NULL, self::RESOLVED_CONTACT_ID)
      ->willReturn(ValidationResult::new());

    // Read only field "bar" should be dropped.
    $dataTransformerMock = $this->createMock(DataTransformerInterface::class);
    $dataTransformerMock->expects(static::once())->method('toEntityValues')
      ->with(['foo' => 'bar'], NULL, self::RESOLVED_CONTACT_ID)
      ->willReturn(['foo' => 'bar2']);
    $formSpec->setDataTransformer($dataTransformerMock);

    $this->profileMock->expects(static::once())->method('onPreCreate')
      ->with($arguments, ['foo' => 'bar2'], $entityFields, $formSpec, self::RESOLVED_CONTACT_ID);

    $createdValues = ['id' => 12];
    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with('Entity', ['foo' => 'bar2'], ['checkPermissions' => FALSE])
      ->willReturn(new Result([$createdValues]));
    $createdValues += ['foo' => 'bar2'];

    $this->profileMock->expects(static::once())->method('onPostCreate')
      ->with($arguments, $createdValues, $entityFields, $formSpec, self::RESOLVED_CONTACT_ID);

    $this->profileMock->method('getSaveSuccessMessage')
      ->with($createdValues, NULL, $formData, self::RESOLVED_CONTACT_ID)
      ->willReturn('Ok');

    static::assertEquals(
      ['message' => 'Ok', 'entityId' => 12],
      $this->handler->submitCreateForm($actionMock)
    );
  }

  public function testGetUpdateForm(): void {
    $actionMock = $this->createActionMock(RemoteGetUpdateFormAction::class);
    $actionMock->setId(12);

    $entityValues = ['foo' => 'f'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->mockGetSelectFieldNames(['foo']);
    $this->profileMock->method('isUpdateGranted')
      ->with($entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(GrantResult::newPermitted());

    $this->mockApi4Execute('Entity', 'get', [
      'select' => ['foo'],
      'join' => [],
      'where' => [['id', '=', 12]],
      'checkPermissions' => FALSE,
    ], new Result([$entityValues]));

    $this->mockApi4Execute('Entity', 'getFields', [
      'loadOptions' => FALSE,
      'values' => ['id' => 12],
      'checkPermissions' => FALSE,
    ], new Result(array_values($entityFields)));

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
    $profileJoin = Join::newWithBridge('JoinedEntity', 'j', 'INNER', 'bridge');
    $this->profileMock->method('getJoins')
      ->with('update', self::RESOLVED_CONTACT_ID)
      ->willReturn([$profileJoin]);

    $profileFilter = Comparison::new('extra', '!=', 'abc');
    $this->profileMock->method('getFilter')
      ->with('update', self::RESOLVED_CONTACT_ID)
      ->willReturn($profileFilter);

    $actionMock = $this->createActionMock(RemoteGetUpdateFormAction::class);
    $actionMock->setId(12);

    $entityValues = ['foo' => 'f'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->mockGetSelectFieldNames(['foo']);
    $this->mockIsUpdateGranted($entityValues, GrantResult::newDeniedWithForm());
    $this->fieldLoadOptionsForFormSpec = TRUE;

    $this->mockApi4Execute('Entity', 'get', [
      'select' => ['foo'],
      'join' => [['JoinedEntity AS j', 'INNER', 'bridge']],
      'where' => [['id', '=', 12], ['extra', '!=', 'abc']],
      'checkPermissions' => FALSE,
    ], new Result([$entityValues]));

    $this->mockApi4Execute('Entity', 'getFields', [
      'loadOptions' => TRUE,
      'values' => ['id' => 12],
      'checkPermissions' => FALSE,
    ], new Result(array_values($entityFields)));

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

    $this->mockGetSelectFieldNames(['foo']);
    $this->mockIsUpdateGranted($entityValues, GrantResult::newDenied('Denied'));

    $this->api4Mock->method('execute')
      ->with('Entity', 'get', [
        'select' => ['foo'],
        'join' => [],
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

  public function testValidateUpdateForm(): void {
    $actionMock = $this->createActionMock(RemoteValidateUpdateFormAction::class);
    $actionMock->setId(12);
    $formData = ['foo' => 'bar'];
    $actionMock->setData($formData);

    $entityValues = ['foo' => 'f', 'bar' => 'b'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->mockGetSelectFieldNames(['foo', 'bar']);
    $this->mockIsUpdateGranted($entityValues, GrantResult::newPermitted());

    $this->mockApi4Execute('Entity', 'get', [
      'select' => ['foo', 'bar'],
      'join' => [],
      'where' => [['id', '=', 12]],
      'checkPermissions' => FALSE,
    ], new Result([$entityValues]));

    $this->mockApi4Execute('Entity', 'getFields', [
      'loadOptions' => FALSE,
      'values' => ['id' => 12],
      'checkPermissions' => FALSE,
    ], new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $formSpec->addElement(new TextField('foo', 'Foo'));

    $this->profileMock->method('getUpdateFormSpec')
      ->with($entityValues, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $validatorMock = $this->createMock(ValidatorInterface::class);
    $formSpec->appendValidator($validatorMock);
    $validatorMock->expects(static::once())->method('validate')
      ->with($formData, $entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(ValidationResult::new(
        ValidationError::new('field', 'invalid1'),
        ValidationError::new('field', 'invalid2'),
      ));

    static::assertSame([
      'valid' => FALSE,
      'errors' => [
        'field' => ['invalid1', 'invalid2'],
      ],
    ], $this->handler->validateUpdateForm($actionMock));
  }

  public function testSubmitUpdateForm(): void {
    $actionMock = $this->createActionMock(RemoteSubmitUpdateFormAction::class);
    $actionMock->setId(12);
    $formData = ['foo' => 'bar', 'bar' => 'baz'];
    $actionMock->setData($formData);

    $entityValues = ['foo' => 'f', 'bar' => 'b'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->mockGetSelectFieldNames(['foo', 'bar']);
    $this->mockIsUpdateGranted($entityValues, GrantResult::newPermitted());

    $this->mockApi4Execute('Entity', 'get', [
      'select' => ['foo', 'bar'],
      'join' => [],
      'where' => [['id', '=', 12]],
      'checkPermissions' => FALSE,
    ], new Result([$entityValues]));

    $this->mockApi4Execute('Entity', 'getFields', [
      'loadOptions' => FALSE,
      'values' => ['id' => 12],
      'checkPermissions' => FALSE,
    ], new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $formSpec->addElement(new TextField('foo', 'Foo'));
    $formSpec->addElement((new TextField('bar', 'Bar'))->setReadOnly(TRUE));

    $this->profileMock->method('getUpdateFormSpec')
      ->with($entityValues, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $validatorMock = $this->createMock(ValidatorInterface::class);
    $formSpec->appendValidator($validatorMock);
    $validatorMock->expects(static::once())->method('validate')
      ->with($formData, $entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(ValidationResult::new());

    // Read only field "bar" should be dropped.
    $dataTransformerMock = $this->createMock(DataTransformerInterface::class);
    $dataTransformerMock->expects(static::once())->method('toEntityValues')
      ->with(['foo' => 'bar'], $entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(['foo' => 'bar2']);
    $formSpec->setDataTransformer($dataTransformerMock);

    $this->profileMock->expects(static::once())->method('onPreUpdate')
      ->with(['foo' => 'bar2'], $entityValues, $entityFields, $formSpec, self::RESOLVED_CONTACT_ID);

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with('Entity', 12, ['foo' => 'bar2'])
      ->willReturn(new Result([['foo' => 'bar2']]));
    $newEntityValues = ['foo' => 'bar2'] + $entityValues;

    $this->profileMock->expects(static::once())->method('onPostUpdate')
      ->with($newEntityValues, $entityValues, $entityFields, $formSpec, self::RESOLVED_CONTACT_ID);

    $this->profileMock->method('getSaveSuccessMessage')
      ->with($newEntityValues, $entityValues, $formData, self::RESOLVED_CONTACT_ID)
      ->willReturn('Ok');

    static::assertSame(['message' => 'Ok'], $this->handler->submitUpdateForm($actionMock));
  }

  /**
   * Test behavior when there are no values to set after call of onPreUpdate().
   */
  public function testSubmitUpdateFormNoUpdateValues(): void {
    $actionMock = $this->createActionMock(RemoteSubmitUpdateFormAction::class);
    $actionMock->setId(12);
    $formData = ['foo' => 'bar', 'bar' => 'baz'];
    $actionMock->setData($formData);

    $entityValues = ['foo' => 'f', 'bar' => 'b'];
    $entityFields = [
      'foo' => ['name' => 'foo'],
      'bar' => ['name' => 'bar'],
    ];

    $this->mockGetSelectFieldNames(['foo', 'bar']);
    $this->mockIsUpdateGranted($entityValues, GrantResult::newPermitted());

    $this->mockApi4Execute('Entity', 'get', [
      'select' => ['foo', 'bar'],
      'join' => [],
      'where' => [['id', '=', 12]],
      'checkPermissions' => FALSE,
    ], new Result([$entityValues]));

    $this->mockApi4Execute('Entity', 'getFields', [
      'loadOptions' => FALSE,
      'values' => ['id' => 12],
      'checkPermissions' => FALSE,
    ], new Result(array_values($entityFields)));

    $formSpec = new FormSpec('Title');
    $formSpec->addElement(new TextField('foo', 'Foo'));
    $formSpec->addElement((new TextField('bar', 'Bar'))->setReadOnly(TRUE));

    $this->profileMock->method('getUpdateFormSpec')
      ->with($entityValues, $entityFields, self::RESOLVED_CONTACT_ID)
      ->willReturn($formSpec);

    $validatorMock = $this->createMock(ValidatorInterface::class);
    $formSpec->appendValidator($validatorMock);
    $validatorMock->expects(static::once())->method('validate')
      ->with($formData, $entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(ValidationResult::new());

    // Read only field "bar" should be dropped.
    $dataTransformerMock = $this->createMock(DataTransformerInterface::class);
    $dataTransformerMock->expects(static::once())->method('toEntityValues')
      ->with(['foo' => 'bar'], $entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn(['foo' => 'bar2']);
    $formSpec->setDataTransformer($dataTransformerMock);

    $this->profileMock->expects(static::once())->method('onPreUpdate')
      ->with(['foo' => 'bar2'], $entityValues, $entityFields, $formSpec, self::RESOLVED_CONTACT_ID)
      ->willReturnCallback(function (array &$newValues) {
        $newValues = [];
      });

    $this->api4Mock->expects(static::never())->method('updateEntity');
    $this->profileMock->expects(static::never())->method('onPostUpdate');

    $newEntityValues = $entityValues;
    $this->profileMock->method('getSaveSuccessMessage')
      ->with($newEntityValues, $entityValues, $formData, self::RESOLVED_CONTACT_ID)
      ->willReturn('Ok');

    static::assertSame(['message' => 'Ok'], $this->handler->submitUpdateForm($actionMock));
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

  /**
   * @phpstan-param list<string> $fieldNames
   */
  private function mockGetSelectFieldNames(array $fieldNames): void {
    $this->profileMock->expects(static::atLeastOnce())->method('getSelectFieldNames')
      ->with(['*'], 'update', [], self::RESOLVED_CONTACT_ID)
      ->willReturn($fieldNames);
  }

  /**
   * @phpstan-param array<int|string, mixed> $arguments
   */
  private function mockIsCreateGranted(array $arguments, GrantResult $grantResult): void {
    $this->profileMock->expects(static::atLeastOnce())->method('isCreateGranted')
      ->with($arguments, self::RESOLVED_CONTACT_ID)
      ->willReturn($grantResult);
  }

  /**
   * @phpstan-param array<string, mixed> $entityValues
   */
  private function mockIsUpdateGranted(array $entityValues, GrantResult $grantResult): void {
    $this->profileMock->expects(static::atLeastOnce())->method('isUpdateGranted')
      ->with($entityValues, self::RESOLVED_CONTACT_ID)
      ->willReturn($grantResult);
  }

  /**
   * @phpstan-param array<string, mixed> $params
   */
  private function mockApi4Execute(string $entityName, string $actionName, array $params, Result $result): void {
    if (NULL === $this->api4ExecuteStub) {
      // Lazy ReturnValueMap.
      $this->api4ExecuteStub = new class () implements Stub {

        /**
         * @phpstan-var list<list<mixed>>
         */
        public array $valueMap = [];

        /**
         * @inheritDoc
         */
        public function toString(): string {
          return $this->getReturnValueMap()->toString();
        }

        /**
         * @inheritDoc
         *
         * @return mixed
         */
        public function invoke(Invocation $invocation) {
          return $this->getReturnValueMap()->invoke($invocation);
        }

        private function getReturnValueMap(): ReturnValueMap {
          return new ReturnValueMap($this->valueMap);
        }

      };

      $this->api4Mock->expects(static::atLeastOnce())
        ->method('execute')
        ->will($this->api4ExecuteStub);
    }

    // @phpstan-ignore-next-line
    $this->api4ExecuteStub->valueMap[] = [
      $entityName,
      $actionName,
      $params,
      $result,
    ];
  }

}
