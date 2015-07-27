<?php
/**
 * @author Robin McCorkell <rmccorkell@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_External\Tests;

use \OCA\Files_External\Lib\BackendConfig;
use \OCA\Files_External\Lib\BackendDependency;

class BackendConfigTest extends \Test\TestCase {

	public function testJsonSerialization() {
		$param = $this->getMockBuilder('\OCA\Files_External\Lib\BackendParameter')
			->disableOriginalConstructor()
			->getMock();
		$param->method('getName')->willReturn('foo');

		$backendConfig = new BackendConfig('\OC\Files\Storage\SMB', 'smb', [$param]);
		$backendConfig->setPriority(123);
		$backendConfig->setCustomJs('foo/bar.js');
		$backendConfig->addAuthScheme('foopass');
		$backendConfig->addAuthScheme('barauth');

		$json = $backendConfig->jsonSerialize();

		$this->assertEquals('smb', $json['backend']);
		$this->assertEquals(123, $json['priority']);
		$this->assertEquals('foo/bar.js', $json['custom']);

		$configuration = $json['configuration'];
		$this->assertArrayHasKey('foo', $configuration);

		$this->assertContains('foopass', $json['authSchemes']);
		$this->assertContains('barauth', $json['authSchemes']);
	}

	public function validateStorageProvider() {
		return [
			[true, ['foo' => true, 'bar' => true, 'baz' => true]],
			[false, ['foo' => true, 'bar' => false]]
		];
	}

	/**
	 * @dataProvider validateStorageProvider
	 */
	public function testValidateStorage($expectedSuccess, $params) {
		$backendParams = [];
		foreach ($params as $name => $valid) {
			$param = $this->getMockBuilder('\OCA\Files_External\Lib\BackendParameter')
				->disableOriginalConstructor()
				->getMock();
			$param->method('getName')
				->willReturn($name);
			$param->expects($this->once())
				->method('validateValue')
				->willReturn($valid);
			$backendParams[] = $param;
		}

		$storageConfig = $this->getMockBuilder('\OCA\Files_External\Lib\StorageConfig')
			->disableOriginalConstructor()
			->getMock();
		$storageConfig->expects($this->once())
			->method('getBackendOptions')
			->willReturn([]);

		$backendConfig = new BackendConfig('\OC\Files\Storage\SMB', 'smb', $backendParams);
		$this->assertEquals($expectedSuccess, $backendConfig->validateStorage($storageConfig));
	}

	public function testCheckDependencies() {
		$backend = new BackendConfig('\OC\Files\Storage\SMB', 'test', []);
		$backend->setDependencyCheck(function() {
			return [
				(new BackendDependency('dependency'))->setMessage('missing dependency'),
				(new BackendDependency('program'))->setMessage('cannot find program'),
			];
		});

		$dependencies = $backend->checkDependencies();
		$this->assertCount(2, $dependencies);
		$this->assertEquals('dependency', $dependencies[0]->getDependency());
		$this->assertEquals('missing dependency', $dependencies[0]->getMessage());
		$this->assertEquals('program', $dependencies[1]->getDependency());
		$this->assertEquals('cannot find program', $dependencies[1]->getMessage());
	}

}
