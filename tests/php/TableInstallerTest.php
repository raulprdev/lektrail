<?php

namespace Completionist\Tests;

use Completionist\TableInstaller;
use Completionist\Tests\Mocks\MockDatabase;
use PHPUnit\Framework\TestCase;

class TableInstallerTest extends TestCase
{
    private MockDatabase $db;
    private TableInstaller $tableInstaller;

    protected function setUp(): void
    {
        $this->db = new MockDatabase();
        $this->db->prefix = 'wp_';
        $this->tableInstaller = new TableInstaller($this->db);
    }

    public function testCreateTableGeneratesCorrectSql(): void
    {
        $this->tableInstaller->createTable();

        $sql = $this->db->tables[0];
        $this->assertStringContainsString('CREATE TABLE', $sql);
        $this->assertStringContainsString('wp_completionist_history', $sql);
        $this->assertStringContainsString('user_id', $sql);
        $this->assertStringContainsString('post_id', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('PRIMARY KEY', $sql);
        $this->assertStringContainsString('UNIQUE KEY', $sql);
    }

    public function testDropTablePassesCorrectTableName(): void
    {
        $this->tableInstaller->dropTable();

        $tableName = $this->db->droppedTables[0];
        $this->assertEquals('wp_completionist_history', $tableName);
    }

    public function testUsesCorrectPrefix(): void
    {
        $this->db->prefix = 'custom_';
        $installer = new TableInstaller($this->db);

        $installer->createTable();

        $sql = $this->db->tables[0];
        $this->assertStringContainsString('custom_completionist_history', $sql);
    }
}