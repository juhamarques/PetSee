document.addEventListener('DOMContentLoaded', () => {
    const radioButtons = document.querySelectorAll('input[name="status"]');
    const forms = document.querySelectorAll('.pet-form');

    const requiredFieldsMap = {
        perdido: ['nome', 'especie', 'sexo', 'local', 'rua', 'numero', 'bairro', 'cidade', 'data', 'telefone', 'foto'],
        resgatado: ['nome_res', 'especie_res', 'sexo_res', 'local_res', 'rua_res', 'numero_res', 'bairro_res', 'cidade_res', 'data_res', 'telefone_res', 'foto_res'],
        adocao: ['especie_ado', 'sexo_ado', 'local_ado', 'rua_ado', 'numero_ado', 'bairro_ado', 'cidade_ado', 'telefone_ado', 'detalhes_ado', 'foto_ado']
    };

    const showForm = (formId) => {
        forms.forEach(form => form.classList.remove('active'));
        const targetForm = document.getElementById(formId);
        if (targetForm) targetForm.classList.add('active');
    };

    const updateRequiredFields = (status) => {
        forms.forEach(form => {
            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.required = false;
            });
        });

        const fields = requiredFieldsMap[status] || [];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.required = true;
        });
    };

    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            const status = radio.value;
            showForm(`form-${status}`);
            updateRequiredFields(status);
        });
    });

    showForm('form-perdido');
    updateRequiredFields('perdido');
});