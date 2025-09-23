/**
 * Funções de validação para o módulo de recrutamento
 * Este arquivo contém as funções de validação referenciadas no template
 */

// Validação de campo obrigatório
function validarCampoObrigatorio(event, minLength = 1) {
    const campo = event.target;
    const valor = campo.value.trim();
    
    if (valor.length < minLength) {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        return true;
    }
}

// Validação de campo genérico
function validarCampo(event, minLength = 1) {
    const campo = event.target;
    const valor = campo.value.trim();
    
    if (valor.length > 0 && valor.length < minLength) {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        return true;
    }
}

// Validação de CPF
function validarCPF(event) {
    const campo = event.target;
    const cpf = campo.value.replace(/\D/g, '');
    
    if (cpf.length === 0) {
        campo.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    if (cpf.length !== 11 || !validarCPFNumerico(cpf)) {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        return true;
    }
}

// Validação numérica do CPF
function validarCPFNumerico(cpf) {
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    // Validação do algoritmo do CPF
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = 11 - (soma % 11);
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(9))) return false;
    
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = 11 - (soma % 11);
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(10))) return false;
    
    return true;
}

// Validação de data
function validarData(event) {
    const campo = event.target;
    const data = campo.value.trim();
    
    if (data.length === 0) {
        campo.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    // Validação básica de formato DD/MM/AAAA
    const regexData = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    const match = data.match(regexData);
    
    if (!match) {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    }
    
    const dia = parseInt(match[1]);
    const mes = parseInt(match[2]);
    const ano = parseInt(match[3]);
    
    // Validação básica de data
    if (dia < 1 || dia > 31 || mes < 1 || mes > 12 || ano < 1900 || ano > new Date().getFullYear()) {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    }
    
    campo.classList.remove('is-invalid');
    campo.classList.add('is-valid');
    return true;
}

// Validação de e-mail
function validarEmail(event) {
    const campo = event.target;
    const email = campo.value.trim();
    
    if (email.length === 0) {
        campo.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!regexEmail.test(email)) {
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        return true;
    }
}

// Função para limpar validações
function limparValidacoes() {
    const campos = document.querySelectorAll('.is-invalid, .is-valid');
    campos.forEach(campo => {
        campo.classList.remove('is-invalid', 'is-valid');
    });
}

// Função para validar formulário completo
function validarFormulario() {
    const camposObrigatorios = document.querySelectorAll('[required]');
    let valido = true;
    
    camposObrigatorios.forEach(campo => {
        if (!validarCampoObrigatorio({ target: campo })) {
            valido = false;
        }
    });
    
    return valido;
}

// Exportar funções para uso global
window.validarCampoObrigatorio = validarCampoObrigatorio;
window.validarCampo = validarCampo;
window.validarCPF = validarCPF;
window.validarData = validarData;
window.validarEmail = validarEmail;
window.limparValidacoes = limparValidacoes;
window.validarFormulario = validarFormulario;
