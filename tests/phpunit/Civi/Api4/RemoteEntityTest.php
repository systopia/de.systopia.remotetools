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
use Civi\RemoteTools\EntityProfile\TestRemoteProductReadOnlyEntityProfile;
use Civi\RemoteTools\EntityProfile\TestRemoteProductReadWriteEntityProfile;
use Civi\RemoteTools\Exception\ValidationFailedException;
use Civi\RemoteTools\Fixture\ProductFixture;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\RemoteTools\Api4\AbstractRemoteEntity
 *
 * @group headless
 */
final class RemoteEntityTest extends AbstractRemoteToolsHeadlessTestCase {

  use ArraySubsetAsserts;

  protected function setUp(): void {
    parent::setUp();
    $this->setUserPermissions(['access TestRemoteProduct']);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteCheckAccessAction
   */
  public function testCheckAccess(): void {
    $result = TestRemoteProduct::checkAccess()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
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
    $result = TestRemoteProduct::checkAccess()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
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
    TestRemoteProduct::checkAccess()
      ->setAction('get')
      ->addValue('id', 1)
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteDeleteAction
   */
  public function testDelete(): void {
    $product1 = ProductFixture::addProduct();
    $product2 = ProductFixture::addProduct();

    $result = TestRemoteProduct::delete()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->addWhere('id', '=', $product1['id'])
      ->execute();
    static::assertArraySubset([['id' => $product1['id']]], $result->getArrayCopy());

    static::assertSame([$product2['id']], Product::get(FALSE)->execute()->column('id'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteDeleteAction
   */
  public function testDeletePermissionMissing(): void {
    $product = ProductFixture::addProduct();

    $result = TestRemoteProduct::delete()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->addWhere('id', '=', $product['id'])
      ->execute();
    static::assertSame([], $result->getArrayCopy());

    static::assertSame([$product['id']], Product::get(FALSE)->execute()->column('id'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteDeleteAction
   */
  public function testDeleteWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteProduct::delete()
      ->addWhere('id', '=', 12)
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetActions
   */
  public function testGetActions(): void {
    $result = TestRemoteProduct::getActions()->execute();
    static::assertEquals([
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
    $result = TestRemoteProduct::getFields()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->execute();

    $expectedFieldNames = Product::getFields(FALSE)->execute()->column('name');
    static::assertArraySubset($expectedFieldNames, $result->column('name'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction
   */
  public function testGetFieldsWithoutProfile(): void {
    $result = TestRemoteProduct::getFields()->execute();
    static::assertCount(1, $result);
    static::assertSame(['id'], $result->column('name'));
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetAction
   */
  public function testGet(): void {
    $result = TestRemoteProduct::get()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->execute();
    static::assertCount(0, $result);

    $product = ProductFixture::addProduct();
    $product = Product::get(FALSE)
      ->addWhere('id', '=', $product['id'])
      ->execute()
      ->single();
    $result = TestRemoteProduct::get()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->execute();
    static::assertCount(1, $result);
    $expectedRemoteProduct = $product + [
      'CAN_delete' => FALSE,
      'CAN_update' => FALSE,
    ];
    static::assertEquals([$expectedRemoteProduct], $result->getArrayCopy());

    $result = TestRemoteProduct::get()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->addWhere('id', '=', $product['id'])
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals([$expectedRemoteProduct], $result->getArrayCopy());

    $result = TestRemoteProduct::get()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->addWhere('id', '=', $product['id'] + 1)
      ->execute();
    static::assertCount(0, $result);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetAction
   */
  public function testGetWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteProduct::get()->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction
   */
  public function testGetCreateForm(): void {
    $result = TestRemoteProduct::getCreateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
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
    TestRemoteProduct::getCreateForm()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction
   */
  public function testGetCreateFormWithoutProfile(): void {
    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    $result = TestRemoteProduct::getCreateForm()
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction
   */
  public function testValidateCreateForm(): void {
    $result = TestRemoteProduct::validateCreateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['foo' => 'bar'])
      ->execute();
    static::assertFalse($result['valid']);
    // "name" is required, additional properties are not allowed.
    // @phpstan-ignore-next-line
    static::assertCount(2, $result['errors']['']);

    $result = TestRemoteProduct::validateCreateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['name' => 'bar'])
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
    TestRemoteProduct::validateCreateForm()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
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
    TestRemoteProduct::validateCreateForm()
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateForm(): void {
    $result = TestRemoteProduct::submitCreateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['name' => 'bar'])
      ->execute();
    static::assertSame(['message' => 'Saved successfully'], $result->getArrayCopy());

    $result = Product::get(FALSE)
      ->addWhere('name', '=', 'bar')
      ->execute();
    static::assertCount(1, $result);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateFormUnauthorized(): void {
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create entity is missing');
    TestRemoteProduct::submitCreateForm()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
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
    TestRemoteProduct::submitCreateForm()
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction
   */
  public function testSubmitCreateFormInvalidData(): void {
    $this->expectException(ValidationFailedException::class);
    TestRemoteProduct::submitCreateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setArguments(['x' => 'y'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateForm(): void {
    $product = ProductFixture::addProduct();
    $result = TestRemoteProduct::getUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'])
      ->execute();
    $resultJson = json_encode($result->getArrayCopy(), JSON_THROW_ON_ERROR);
    static::assertStringContainsString('Update Form Title', $resultJson);
    static::assertStringContainsString('Enter Name', $resultJson);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateFormUnauthorized(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteProduct::getUpdateForm()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->setId($product['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateFormEntityMissing(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteProduct::getUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'] + 1)
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction
   */
  public function testGetUpdateFormWithoutProfile(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteProduct::getUpdateForm()
      ->setId($product['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateForm(): void {
    $product = ProductFixture::addProduct();

    $result = TestRemoteProduct::validateUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'])
      ->setData(['name' => 'x'])
      ->execute();

    static::assertFalse($result['valid']);
    // "name" has to be at least 2 characters.
    // @phpstan-ignore-next-line
    static::assertCount(1, $result['errors']['name']);

    $result = TestRemoteProduct::validateUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'])
      ->setData(['name' => 'xy'])
      ->execute();

    static::assertTrue($result['valid']);
    static::assertSame([], $result['errors']);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateFormUnauthorized(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteProduct::validateUpdateForm()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->setId($product['id'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateFormEntityMissing(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteProduct::validateUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'] + 1)
      ->setData(['name' => 'test'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction
   */
  public function testValidateUpdateFormWithoutProfile(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteProduct::validateUpdateForm()
      ->setId($product['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateForm(): void {
    $product = ProductFixture::addProduct();

    $result = TestRemoteProduct::submitUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'])
      ->setData(['name' => 'xy'])
      ->execute();
    static::assertSame(['message' => 'Saved successfully'], $result->getArrayCopy());

    $result = Product::get(FALSE)
      ->addWhere('name', '=', 'xy')
      ->execute();
    static::assertCount(1, $result);
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormUnauthorized(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteProduct::submitUpdateForm()
      ->setProfile(TestRemoteProductReadOnlyEntityProfile::NAME)
      ->setId($product['id'])
      ->setData(['foo' => 'bar'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormEntityMissing(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to update entity is missing');
    TestRemoteProduct::submitUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'] + 1)
      ->setData(['name' => 'test'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormWithoutProfile(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Parameter "profile" is required.');
    TestRemoteProduct::submitUpdateForm()
      ->setId($product['id'])
      ->execute();
  }

  /**
   * @covers \Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction
   */
  public function testSubmitUpdateFormInvalidData(): void {
    $product = ProductFixture::addProduct();

    $this->expectException(ValidationFailedException::class);
    TestRemoteProduct::submitUpdateForm()
      ->setProfile(TestRemoteProductReadWriteEntityProfile::NAME)
      ->setId($product['id'])
      ->setData(['name' => 'x'])
      ->execute();
  }

}
