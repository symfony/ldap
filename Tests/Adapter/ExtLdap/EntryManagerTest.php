<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Ldap\Tests\Adapter\ExtLdap;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Adapter\ExtLdap\Connection;
use Symfony\Component\Ldap\Adapter\ExtLdap\EntryManager;
use Symfony\Component\Ldap\Entry;

class EntryManagerTest extends TestCase
{
    public function testMove()
    {
        $this->expectException('Symfony\Component\Ldap\Exception\LdapException');
        $this->expectExceptionMessage('Entry "$$$$$$" malformed, could not parse RDN.');
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('isBound')->willReturn(true);

        $entry = new Entry('$$$$$$');
        $entryManager = new EntryManager($connection);
        $entryManager->move($entry, 'a');
    }

    /**
     * @see https://tools.ietf.org/html/rfc4514#section-3
     * @dataProvider getDataProviderDns
     */
    public function testMoveWithRFC4514DistinguishedName($testDn, $expectedRdn)
    {
        $connection = $this->createMock(Connection::class);

        $entry = new Entry($testDn);
        $entryManager = new EntryManager($connection);

        $method = (new \ReflectionClass(EntryManager::class))->getMethod('parseRdnFromEntry');
        $method->setAccessible(true);

        $cn = $method->invokeArgs($entryManager, [$entry, 'a']);

        self::assertSame($expectedRdn, $cn);
    }

    /**
     * Data provder for testMoveWithRFC4514DistinguishedName
     */
    public function getDataProviderDns(): array {

        return [
            ['CN=Simple,DC=example,DC=net', 'CN=Simple'],
            ['CN=James \"Jim\" Smith\, III,DC=example,DC=net', 'CN=James \"Jim\" Smith\, III'],
            ['UID=jsmith,DC=example,DC=net', 'UID=jsmith'],
            ["CN=Before\0dAfter,DC=example,DC=net", "CN=Before\0dAfter"],
        ];

    }

    public function testGetResources()
    {
        $this->expectException('Symfony\Component\Ldap\Exception\NotBoundException');
        $this->expectExceptionMessage('Query execution is not possible without binding the connection first.');
        $connection = $this->getMockBuilder(Connection::class)->getMock();
        $connection
            ->expects($this->once())
            ->method('isBound')->willReturn(false);

        $entry = new Entry('$$$$$$');
        $entryManager = new EntryManager($connection);
        $entryManager->update($entry);
    }
}
