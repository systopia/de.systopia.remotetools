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

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\RemoteTools\AbstractRemoteToolsHeadlessTestCase;
use Civi\RemoteTools\EntityProfile\TestRemoteGroupReadOnlyEntityProfile;
use Civi\RemoteTools\EntityProfile\TestRemoteGroupReadWriteEntityProfile;
use Civi\RemoteTools\Exception\ValidationFailedException;
use Civi\RemoteTools\Fixture\GroupFixture;
use Civi\RemoteTools\PHPUnit\Traits\ArrayAssertTrait;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\RemoteTools\Api4\AbstractRemoteEntity
 *
 * @group headless
 */
final class RemoteEntityTest extends AbstractRemoteToolsHeadlessTestCase {

  use ArraySubsetAsserts;

  use ArrayAssertTrait;

  protected function setUp(): void {
    parent::setUp();
    $this->setUserPermissions(['access TestRemoteGroup']);
    Group::delete(FALSE)
      ->addWhere('id', 'IS NOT NULL')
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteCheckAccessAction
   */
  public function testCheckAccess(): void {
    $result = TestRemoteGroup::checkAccess()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setAction('get')
      ->addValue('id', 1)
      ->execute();

    static::assertSame([['access' => TRUE]], $result->getArrayCopy());
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteCheckAccessAction
   */
  public function testCheckAccessPermissionMissing(): void {
    $this->setUserPermissions(['access CiviCRM']);
    $result = TestRemoteGroup::checkAccess()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setAction('get')
      ->addValue('id', 1)
      ->execute();

    static::assertSame([['access' => FALSE]], $result->getArrayCopy());
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteCheckAccessAction
   */
  public function testCheckAccessWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::checkAccess()
      ->setAction('get')
      ->addValue('id', 1)
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteDeleteAction
   */
  public function testDelete(): void {
    $group1 = GroupFixture::addGroup();
    $group2 = GroupFixture::addGroup();

    $result = TestRemoteGroup::delete()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->addWhere('id', '=', $group1['id'])
      ->execute();
    static::assertArraySubset([['id' => $group1['id']]], $result->getArrayCopy());

    static::assertSame([$group2['id']], Group::get(FALSE)->execute()->column('id'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteDeleteAction
   */
  public function testDeletePermissionMissing(): void {
    $group = GroupFixture::addGroup();

    $result = TestRemoteGroup::delete()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->addWhere('id', '=', $group['id'])
      ->execute();
    static::assertSame([], $result->getArrayCopy());

    static::assertSame([$group['id']], Group::get(FALSE)->execute()->column('id'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteDeleteAction
   */
  public function testDeleteWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::delete()
      ->addWhere('id', '=', 12)
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetActions
   */
  public function testGetActions(): void {
    $result = TestRemoteGroup::getActions()->execute();
    static::assertArrayHasAllValues([
      'checkAccess',
      'delete',
      'get',
      'getActions',
      'getCreateForm',
      'getFields',
      'getUpdateForm',
      'submitCreateForm',
      'submitUpdateForm',
      'validateCreateForm',
      'validateUpdateForm',
    ], $result->column('name'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction
   */
  public function testGetFields(): void {
    $result = TestRemoteGroup::getFields()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->execute();

    $expectedFieldNames = Group::getFields(FALSE)->execute()->column('name');
    static::assertArraySubset($expectedFieldNames, $result->column('name'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction
   */
  public function testGetFieldsWithoutProfile(): void {
    $result = TestRemoteGroup::getFields()->execute();
    static::assertCount(1, $result);
    static::assertSame(['id'], $result->column('name'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetAction
   */
  public function testGet(): void {
    $result = TestRemoteGroup::get()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->execute();
    static::assertCount(0, $result);

    $group = GroupFixture::addGroup();
    $group = Group::get(FALSE)
      ->addWhere('id', '=', $group['id'])
      ->execute()
      ->single();
    $result = TestRemoteGroup::get()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->addSelect('*', 'CAN_delete', 'CAN_update')
      ->execute();
    static::assertCount(1, $result);
    $expectedRemoteGroup = $group + [
      'CAN_delete' => FALSE,
      'CAN_update' => FALSE,
    ];
    static::assertEquals([$expectedRemoteGroup], $result->getArrayCopy());

    $result = TestRemoteGroup::get()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->addWhere('id', '=', $group['id'])
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals([$group], $result->getArrayCopy());

    $result = TestRemoteGroup::get()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->addWhere('id', '=', $group['id'] + 1)
      ->execute();
    static::assertCount(0, $result);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetAction
   */
  public function testGetWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::get()->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction
   */
  public function testGetCreateForm(): void {
    $result = TestRemoteGroup::getCreateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->execute();
    $resultJson = json_encode($result->getArrayCopy(), JSON_THROW_ON_ERROR);
    static::assertStringContainsString('Create Form Title', $resultJson);
    static::assertStringContainsString('Enter Name', $resultJson);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction
   */
  public function testGetCreateFormUnauthorized(): void {
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create entity is missing');
    TestRemoteGroup::getCreateForm()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction
   */
  public function testGetCreateFormWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    $result = TestRemoteGroup::getCreateForm()
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction
   */
  public function testValidateCreateForm(): void {
    $result = TestRemoteGroup::validateCreateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['name' => 'bar', 'foo' => 'bar'])
      ->execute();
    static::assertFalse($result['valid']);
    // "title" is required.
    // There is no error for the additional property (foo) because of
    // https://github.com/systopia/opis-json-schema-ext/pull/40
    // @phpstan-ignore offsetAccess.nonOffsetAccessible
    static::assertIsArray($result['errors']['']);
    static::assertCount(1, $result['errors']['']);
    static::assertIsString($result['errors'][''][0]);
    static::assertStringContainsString('title', $result['errors'][''][0]);

    $result = TestRemoteGroup::validateCreateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['name' => 'bar', 'title' => 'Bar', 'foo' => 'bar'])
      ->execute();
    static::assertFalse($result['valid']);
    // Additional properties (foo) are not allowed.
    // @phpstan-ignore offsetAccess.nonOffsetAccessible
    static::assertIsArray($result['errors']['']);
    static::assertCount(1, $result['errors']['']);
    static::assertIsString($result['errors'][''][0]);
    static::assertStringContainsString('foo', $result['errors'][''][0]);

    $result = TestRemoteGroup::validateCreateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['name' => 'bar', 'title' => 'Bar'])
      ->execute();

    static::assertTrue($result['valid']);
    static::assertSame([], $result['errors']);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction
   */
  public function tesValidateCreateFormUnauthorized(): void {
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create entity is missing');
    TestRemoteGroup::validateCreateForm()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction
   */
  public function testValidateCreateFormWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::validateCreateForm()
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateForm(): void {
    $result = TestRemoteGroup::submitCreateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['name' => 'bar', 'title' => 'Bar'])
      ->execute();
    static::assertEquals(['message', 'entityId'], array_keys($result->getArrayCopy()));
    static::assertSame('Saved successfully', $result['message']);
    static::assertIsInt($result['entityId']);
    $entityId = $result['entityId'];

    $result = Group::get(FALSE)
      ->addWhere('name', '=', 'bar')
      ->addWhere('title', '=', 'Bar')
      ->execute();
    static::assertCount(1, $result);
    static::assertSame($entityId, $result->single()['id']);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateFormUnauthorized(): void {
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create entity is missing');
    TestRemoteGroup::submitCreateForm()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateFormWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::submitCreateForm()
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateFormInvalidData(): void {
    $this->expectException(ValidationFailedException::class);
    TestRemoteGroup::submitCreateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateForm(): void {
    $group = GroupFixture::addGroup();
    $result = TestRemoteGroup::getUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'])
      ->execute();
    $resultJson = json_encode($result->getArrayCopy(), JSON_THROW_ON_ERROR);
    static::assertStringContainsString('Update Form Title', $resultJson);
    static::assertStringContainsString('Enter Name', $resultJson);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateFormUnauthorized(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteGroup::getUpdateForm()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setId($group['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateFormEntityMissing(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteGroup::getUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'] + 1)
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateFormWithoutProfile(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::getUpdateForm()
      ->setId($group['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateForm(): void {
    $group = GroupFixture::addGroup();

    $result = TestRemoteGroup::validateUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'])
      ->setData(['name' => 'x'])
      ->execute();

    static::assertFalse($result['valid']);
    // "name" has to be at least 2 characters.
    // @phpstan-ignore-next-line
    static::assertCount(1, $result['errors']['name']);

    $result = TestRemoteGroup::validateUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'])
      ->setData(['name' => 'xy'])
      ->execute();

    static::assertTrue($result['valid']);
    static::assertSame([], $result['errors']);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateFormUnauthorized(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteGroup::validateUpdateForm()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setId($group['id'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateFormEntityMissing(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteGroup::validateUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'] + 1)
      ->setData(['name' => 'test'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateFormWithoutProfile(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::validateUpdateForm()
      ->setId($group['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateForm(): void {
    $group = GroupFixture::addGroup();

    $result = TestRemoteGroup::submitUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'])
      ->setData(['name' => 'xy'])
      ->execute();
    static::assertSame(
      ['message' => 'Saved successfully', 'entityId' => $group['id']],
      $result->getArrayCopy()
    );

    $result = Group::get(FALSE)
      ->addWhere('name', '=', 'xy')
      ->execute();
    static::assertCount(1, $result);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormUnauthorized(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteGroup::submitUpdateForm()
      ->setProfile(TestRemoteGroupReadOnlyEntityProfile::NAME)
      ->setId($group['id'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormEntityMissing(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteGroup::submitUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'] + 1)
      ->setData(['name' => 'test'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormWithoutProfile(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteGroup::submitUpdateForm()
      ->setId($group['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormInvalidData(): void {
    $group = GroupFixture::addGroup();

    $this->expectException(ValidationFailedException::class);
    TestRemoteGroup::submitUpdateForm()
      ->setProfile(TestRemoteGroupReadWriteEntityProfile::NAME)
      ->setId($group['id'])
      ->setData(['name' => 'x'])
      ->execute();
  }

}
