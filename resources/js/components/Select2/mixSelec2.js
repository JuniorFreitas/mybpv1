const configselect2 = {
    // computed: {
    //     settings2() {
    //         return {
    //             width: "100%",
    //             placeholder: "Todos",
    //             multiple: false,
    //             allowClear: true,
    //             closeOnSelect: true,
    //             dropdownAutoWidth: true,
    //             dropdownCss: {
    //                 "z-index": "99999999999999",
    //             },
    //             language: {
    //                 noResults: function () {
    //                     return 'Nenhum resultado encontrado';
    //                 }
    //             }
    //         };
    //     }
    // },
    data() {
        return {
            settings2: {
                width: "100%",
                placeholder: "Todos",
                multiple: false,
                allowClear: true,
                closeOnSelect: true,
                dropdownAutoWidth: true,
                dropdownCss: {
                    "z-index": "99999999999999",
                },
                language: {
                    noResults: function () {
                        return 'Nenhum resultado encontrado';
                    }
                }
            },
        };
    }
};

export default configselect2;
