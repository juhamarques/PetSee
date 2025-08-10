document.addEventListener('DOMContentLoaded', () => {
    const radioButtons = document.querySelectorAll('input[name="tipo_cadastro"]');
    const cadastroPessoalDiv = document.getElementById('cadastro-pessoal');
    const cadastroComercialDiv = document.getElementById('cadastro-comercial');
    
    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'pessoal') {
                cadastroPessoalDiv.style.display = 'block';
                cadastroComercialDiv.style.display = 'none';
                
                document.getElementById('nome').required = true;
                document.getElementById('email').required = true;
                document.getElementById('nascimento').required = true;
                document.getElementById('cpf').required = true;
                document.getElementById('cep').required = true;
                document.getElementById('senha').required = true;

                document.getElementById('nome_empresa').required = false;
                document.getElementById('email_comercial').required = false;
                document.getElementById('cnpj').required = false;
                document.getElementById('cep_comercial').required = false;
                document.getElementById('senha_comercial').required = false;

            } else if (radio.value === 'comercial') {
                cadastroPessoalDiv.style.display = 'none';
                cadastroComercialDiv.style.display = 'block';

                document.getElementById('nome').required = false;
                document.getElementById('email').required = false;
                document.getElementById('nascimento').required = false;
                document.getElementById('cpf').required = false;
                document.getElementById('cep').required = false;
                document.getElementById('senha').required = false;

                document.getElementById('nome_empresa').required = true;
                document.getElementById('email_comercial').required = true;
                document.getElementById('cnpj').required = true;
                document.getElementById('cep_comercial').required = true;
                document.getElementById('senha_comercial').required = true;
            }
        });
    });
});