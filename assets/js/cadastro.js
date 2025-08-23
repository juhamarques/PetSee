document.addEventListener('DOMContentLoaded', () => {
    const radioButtons = document.querySelectorAll('input[name="tipo_cadastro"]');
    const formPessoal = document.getElementById('cadastro-pessoal');
    const formComercial = document.getElementById('cadastro-comercial');

    const updateForms = (tipo) => {
        formPessoal.style.display = 'none';
        formComercial.style.display = 'none';

        document.querySelectorAll('#cadastro-pessoal input, #cadastro-pessoal select, #cadastro-comercial input, #cadastro-comercial select').forEach(field => {
            field.required = false;
        });

        if (tipo === 'pessoal') {
            formPessoal.style.display = 'block';
            document.querySelectorAll('#cadastro-pessoal input, #cadastro-pessoal select').forEach(field => {
                field.required = true;
            });
        } else {
            formComercial.style.display = 'block';
            document.querySelectorAll('#cadastro-comercial input, #cadastro-comercial select').forEach(field => {
                field.required = true;
            });
        }
    };

    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => updateForms(radio.value));
    });

    const initialValue = document.querySelector('input[name="tipo_cadastro"]:checked').value;
    updateForms(initialValue);
});