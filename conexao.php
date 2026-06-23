<?php
// ============================================================
// CONEXÃO COM O BANCO DE DADOS
// ============================================================

$host = 'localhost';           // Hostinger usa localhost
$usuario = 'SEU_USUARIO';      // Usuário do banco (ex: u123456_homecare)
$senha = 'SUA_SENHA';          // Senha do banco
$banco = 'SEU_BANCO';          // Nome do banco (ex: u123456_homecare_db)

// Criar conexão
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($conn->connect_error) {
    die("❌ Erro de conexão: " . $conn->connect_error);
}

// Definir charset para UTF-8
$conn->set_charset("utf8");

// ============================================================
// FUNÇÕES AUXILIARES
// ============================================================

// 1. CADASTRAR USUÁRIO
function cadastrarUsuario($nome, $email, $senha, $tipo, $telefone = '') {
    global $conn;
    
    $senhaHash = md5($senha); // Em produção, use password_hash()
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo, telefone) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nome, $email, $senhaHash, $tipo, $telefone);
    
    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// 2. LOGIN
function loginUsuario($email, $senha) {
    global $conn;
    
    $senhaHash = md5($senha);
    $sql = "SELECT * FROM usuarios WHERE email = ? AND senha = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senhaHash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => true, 'user' => $result->fetch_assoc()];
    } else {
        return ['success' => false, 'error' => 'E-mail ou senha inválidos'];
    }
}

// 3. LISTAR PACIENTES
function listarPacientes($cuidadorId) {
    global $conn;
    
    $sql = "SELECT * FROM pacientes WHERE cuidador_id = ? ORDER BY criado_em DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cuidadorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pacientes = [];
    while ($row = $result->fetch_assoc()) {
        $pacientes[] = $row;
    }
    
    return ['success' => true, 'pacientes' => $pacientes];
}

// 4. ADICIONAR PACIENTE
function adicionarPaciente($cuidadorId, $nome, $idade, $condicao, $medicamentos, $responsavel, $telefone) {
    global $conn;
    
    $sql = "INSERT INTO pacientes (cuidador_id, nome, idade, condicao, medicamentos, responsavel, telefone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $cuidadorId, $nome, $idade, $condicao, $medicamentos, $responsavel, $telefone);
    
    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// 5. LISTAR SERVIÇOS
function listarServicos() {
    global $conn;
    
    $sql = "SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome";
    $result = $conn->query($sql);
    
    $servicos = [];
    while ($row = $result->fetch_assoc()) {
        $servicos[] = $row;
    }
    
    return ['success' => true, 'servicos' => $servicos];
}

// 6. ADICIONAR ANOTAÇÃO
function adicionarAnotacao($pacienteId, $cuidadorId, $tipo, $titulo, $descricao, $duracao = 0) {
    global $conn;
    
    $sql = "INSERT INTO anotacoes (paciente_id, cuidador_id, tipo, titulo, descricao, duracao) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssi", $pacienteId, $cuidadorId, $tipo, $titulo, $descricao, $duracao);
    
    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// 7. ADICIONAR PROGRESSO
function adicionarProgresso($pacienteId, $fisico, $cognitivo, $emocional, $avaliacao, $observacoes) {
    global $conn;
    
    $sql = "INSERT INTO progresso (paciente_id, fisico, cognitivo, emocional, avaliacao, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiss", $pacienteId, $fisico, $cognitivo, $emocional, $avaliacao, $observacoes);
    
    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// 8. OBTER ÚLTIMO PROGRESSO
function getUltimoProgresso($pacienteId) {
    global $conn;
    
    $sql = "SELECT * FROM progresso WHERE paciente_id = ? ORDER BY criado_em DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pacienteId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => true, 'data' => $result->fetch_assoc()];
    } else {
        return ['success' => false, 'error' => 'Nenhum progresso encontrado'];
    }
}

// ============================================================
// RETORNAR CONEXÃO PARA USO EM OUTROS ARQUIVOS
// ============================================================
return $conn;
?>
