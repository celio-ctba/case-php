<?php

use PHPUnit\Framework\TestCase;
use App\User;

class UserTest extends TestCase
{
    private $dbMock;
    private $stmtMock;
    private $resultMock;

    protected function setUp(): void
{
    $this->dbMock = $this->createMock(mysqli::class);
    $this->resultMock = $this->createMock(mysqli_result::class);

    // Criar manualmente a classe fake e armazenar em $this->stmtMock
    $this->stmtMock = new class {
        public int $affected_rows = 1;

        public function bind_param(...$params): void {}
        public function execute(): void {}
    };
}

    public function testCreateUser()
    {
       $input = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123'
    ];

    // Cria o stub do mysqli_stmt
    $stmtStub = $this->createMock(mysqli_stmt::class);

    // Espera que bind_param seja chamado
    $stmtStub->expects($this->once())
        ->method('bind_param')
        ->with(
            'sss',
            $input['name'],
            $input['email'],
            $this->isType('string') // hash da senha
        );

    // Simula execute retornando true
    $stmtStub->expects($this->once())
        ->method('execute')
        ->willReturn(true);

    // Mock de mysqli retornando o stub acima
    $this->dbMock->expects($this->once())
        ->method('prepare')
        ->willReturn($stmtStub);

    $user = new \App\User($this->dbMock);
    $result = $user->create($input);

    $this->assertTrue($result); 
    }

    public function testFindAllUsers()
    {
        $expectedData = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'created_at' => '2025-09-08 10:00:00'],
        ];

        $this->dbMock->expects($this->once())
            ->method('query')
            ->with("SELECT id, name, email, created_at FROM users")
            ->willReturn($this->resultMock);

        $this->resultMock->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn($expectedData);

        $user = new User($this->dbMock);
        $result = $user->findAll();

        $this->assertEquals($expectedData, $result);
    }
}

?>