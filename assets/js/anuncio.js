/*---------------------------
Tipo de cadastro animal
----------------------------*/
document.addEventListener('DOMContentLoaded', () => {
    const radioButtons = document.querySelectorAll('input[name="status"]');
    const forms = document.querySelectorAll('.pet-form');
    
    const showForm = (formId) => {
        forms.forEach(form => {
            form.classList.remove('active');
        });
        document.getElementById(formId).classList.add('active');
    };

    const updateRequiredFields = (status) => {
        forms.forEach(form => {
            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.required = false;
            });
        });
        
        if (status === 'perdido') {
            document.getElementById('nome').required = true;
            document.getElementById('especie').required = true;
            document.getElementById('sexo').required = true;
            document.getElementById('local').required = true;
            document.getElementById('data').required = true;
            document.getElementById('telefone').required = true;
            document.getElementById('foto').required = true;
        } else if (status === 'encontrado') {
            document.getElementById('nome_enc').required = true;
            document.getElementById('especie_enc').required = true;
            document.getElementById('sexo_enc').required = true;
            document.getElementById('local_enc').required = true;
            document.getElementById('data_enc').required = true;
            document.getElementById('telefone_enc').required = true;
            document.getElementById('foto_enc').required = true;
        } else if (status === 'adocao') {
            document.getElementById('especie_ado').required = true;
            document.getElementById('sexo_ado').required = true;
            document.getElementById('telefone_ado').required = true;
            document.getElementById('detalhes_ado').required = true;
            document.getElementById('foto_ado').required = true;
        }
    };

    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            showForm(`form-${radio.value}`);
            updateRequiredFields(radio.value);
        });
    });
    
    // Inicializa a página com o formulário 'perdido'
    showForm('form-perdido');
    updateRequiredFields('perdido');
});