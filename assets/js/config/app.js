const APP = {

    baseUrl:
        '/ministerio',

    csrfToken:
        document.querySelector(
            'meta[name="csrf-token"]'
        )?.content || ''
};

window.APP = APP;