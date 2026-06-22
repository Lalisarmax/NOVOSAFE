// ============================================================
// FIREBASE CONFIGURAÇÃO
// ============================================================

// 🔥 COLE AQUI AS SUAS CREDENCIAIS DO FIREBASE 🔥
const firebaseConfig = {
    apiKey: "SUA_API_KEY_AQUI",
    authDomain: "SEU_PROJETO.firebaseapp.com",
    projectId: "SEU_PROJETO_ID",
    storageBucket: "SEU_PROJETO.appspot.com",
    messagingSenderId: "SEU_SENDER_ID",
    appId: "SEU_APP_ID"
};

// Inicializar Firebase
firebase.initializeApp(firebaseConfig);

// Inicializar serviços
const auth = firebase.auth();
const db = firebase.firestore();

// ============================================================
// FUNÇÕES DE AUTENTICAÇÃO
// ============================================================

async function cadastrarUsuario(email, senha, dados) {
    try {
        const userCredential = await auth.createUserWithEmailAndPassword(email, senha);
        const user = userCredential.user;
        
        await db.collection('users').doc(user.uid).set({
            ...dados,
            email: email,
            uid: user.uid,
            criadoEm: firebase.firestore.FieldValue.serverTimestamp()
        });
        
        return { success: true, user: user };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

async function loginUsuario(email, senha) {
    try {
        const userCredential = await auth.signInWithEmailAndPassword(email, senha);
        return { success: true, user: userCredential.user };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

function logoutUsuario() {
    return auth.signOut();
}

function verificarLogin() {
    return new Promise((resolve) => {
        auth.onAuthStateChanged((user) => {
            resolve(user);
        });
    });
}

async function getUsuarioData(uid) {
    try {
        const doc = await db.collection('users').doc(uid).get();
        if (doc.exists) {
            return { success: true, data: doc.data() };
        } else {
            return { success: false, error: 'Usuário não encontrado' };
        }
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// ============================================================
// FUNÇÕES DE PACIENTES
// ============================================================

async function adicionarPaciente(cuidadorId, dados) {
    try {
        const docRef = await db.collection('pacientes').add({
            ...dados,
            cuidadorId: cuidadorId,
            status: 'ativo',
            criadoEm: firebase.firestore.FieldValue.serverTimestamp()
        });
        return { success: true, id: docRef.id };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

async function listarPacientes(cuidadorId) {
    try {
        const snapshot = await db.collection('pacientes')
            .where('cuidadorId', '==', cuidadorId)
            .orderBy('criadoEm', 'desc')
            .get();
        
        const pacientes = [];
        snapshot.forEach((doc) => {
            pacientes.push({ id: doc.id, ...doc.data() });
        });
        
        return { success: true, pacientes: pacientes };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

async function getPaciente(pacienteId) {
    try {
        const doc = await db.collection('pacientes').doc(pacienteId).get();
        if (doc.exists) {
            return { success: true, data: { id: doc.id, ...doc.data() } };
        } else {
            return { success: false, error: 'Paciente não encontrado' };
        }
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// ============================================================
// FUNÇÕES DE ANOTAÇÕES
// ============================================================

async function adicionarAnotacao(pacienteId, cuidadorId, dados) {
    try {
        const docRef = await db.collection('anotacoes').add({
            pacienteId: pacienteId,
            cuidadorId: cuidadorId,
            ...dados,
            criadoEm: firebase.firestore.FieldValue.serverTimestamp()
        });
        return { success: true, id: docRef.id };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

async function listarAnotacoes(pacienteId) {
    try {
        const snapshot = await db.collection('anotacoes')
            .where('pacienteId', '==', pacienteId)
            .orderBy('criadoEm', 'desc')
            .get();
        
        const anotacoes = [];
        snapshot.forEach((doc) => {
            anotacoes.push({ id: doc.id, ...doc.data() });
        });
        
        return { success: true, anotacoes: anotacoes };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// ============================================================
// FUNÇÕES DE PROGRESSO
// ============================================================

async function adicionarProgresso(pacienteId, dados) {
    try {
        const docRef = await db.collection('progresso').add({
            pacienteId: pacienteId,
            ...dados,
            criadoEm: firebase.firestore.FieldValue.serverTimestamp()
        });
        return { success: true, id: docRef.id };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

async function listarProgresso(pacienteId) {
    try {
        const snapshot = await db.collection('progresso')
            .where('pacienteId', '==', pacienteId)
            .orderBy('criadoEm', 'desc')
            .limit(10)
            .get();
        
        const progressos = [];
        snapshot.forEach((doc) => {
            progressos.push({ id: doc.id, ...doc.data() });
        });
        
        return { success: true, progressos: progressos };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

async function getUltimoProgresso(pacienteId) {
    try {
        const snapshot = await db.collection('progresso')
            .where('pacienteId', '==', pacienteId)
            .orderBy('criadoEm', 'desc')
            .limit(1)
            .get();
        
        if (!snapshot.empty) {
            let data = {};
            snapshot.forEach((doc) => {
                data = { id: doc.id, ...doc.data() };
            });
            return { success: true, data: data };
        } else {
            return { success: false, error: 'Nenhum progresso encontrado' };
        }
    } catch (error) {
        return { success: false, error: error.message };
    }
}
