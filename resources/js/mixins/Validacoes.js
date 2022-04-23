import moment from 'moment';

const validacoes = {
    computed: {
        hash() {
            return `${parseInt(Math.random() * 999999)}`;
        },
    },
    methods: {
        mostraErro(retornoLaravel, titulo = "Ocorreu um erro", quantidade = 3) {
            if (retornoLaravel === undefined) {
                return false;
            }
            let mensagem = "";
            titulo = retornoLaravel.msg ? retornoLaravel.msg : titulo;
            let lista = _.keys(retornoLaravel.erros);
            if (lista.length) {
                mensagem += `<ul>`;
                let total = 1;
                lista.every((key, item) => {
                    let descricao = retornoLaravel.erros[key][0];
                    mensagem += `<li> <strong>${key}:</strong> ${descricao} </li>`;
                    total++;
                    if (total === quantidade) {
                        return false;
                    }
                });
                mensagem += `</ul>`;
            } else {
                mensagem = retornoLaravel.message;
            }
            toastr.error(mensagem, titulo);
        },
        mostraSucesso(mensagem, titulo) {
            toastr.success(mensagem, titulo);
        },
        validaBlur() {
            let el = document.querySelectorAll('.validacampo');
            for (let [key, input] of Object.entries(el)) {
                input.focus();
                input.blur();
            }
        },
        replaceAll(string, token, newtoken) {
            while (string.indexOf(token) !== -1) {
                string = string.replace(token, newtoken);
            }
            return string;
        },
        valida_cpf(evt) {
            let valor = $(evt).val();

            let numeros, digitos, soma, i, resultado, digitos_iguais;
            let cpf;

            $(evt).next("div.invalid-feedback").remove();

            cpf = valor;
            cpf = this.replaceAll(cpf, ".", ""); // tira os pontos
            cpf = this.replaceAll(cpf, "-", ""); // tira o traço
            digitos_iguais = 1;
            if (cpf.length == 0) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            }
            if (cpf.length < 11) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(
                        `<div class="invalid-feedback">O CPF está incompleto.</div>`
                    );
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "O CPF está incompleto.");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            }
            for (i = 0; i < cpf.length - 1; i++)
                if (cpf.charAt(i) != cpf.charAt(i + 1)) {
                    digitos_iguais = 0;
                    break;
                }
            if (!digitos_iguais) {
                numeros = cpf.substring(0, 9);
                digitos = cpf.substring(9);
                soma = 0;
                for (i = 10; i > 1; i--) {
                    soma += numeros.charAt(10 - i) * i;
                    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
                }
                if (resultado != digitos.charAt(0)) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">O CPF está inválido!</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "O CPF está inválido!");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                }
                numeros = cpf.substring(0, 10);
                soma = 0;
                for (i = 11; i > 1; i--) {
                    soma += numeros.charAt(11 - i) * i;
                    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
                }
                if (resultado != digitos.charAt(1)) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">O CPF está inválido!</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "O CPF está inválido!");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                }
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else $(evt).addClass("is-invalid");
            if (
                $(evt).next("div.invalid-feedback").length == 0 &&
                !$(evt).attr("data-toggle")
            ) {
                $(evt).after(
                    `<div class="invalid-feedback">O CPF está inválido!</div>`
                );
            }
            if ($(evt).attr("data-toggle")) {
                $(evt).attr("data-content", "O CPF está inválido!");
                $(evt).popover();
                $(evt).popover("enable");
            }
            return false;
        },
        validaCpfVazio(evt) {
            let numeros, digitos, soma, i, resultado, digitos_iguais;
            let cpf;
            cpf = evt.value;
            cpf = this.replaceAll(cpf, ".", ""); // tira os pontos
            cpf = this.replaceAll(cpf, "-", ""); // tira o traço
            digitos_iguais = 1;
            if (cpf.length == 0) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(
                        `<div class="invalid-feedback">O CPF é obrigatório.</div>`
                    );
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "O CPF é obrigatório.");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            }
            if (cpf.length < 11) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(
                        `<div class="invalid-feedback">O CPF está incompleto.</div>`
                    );
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "O CPF está incompleto.");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            }
            for (i = 0; i < cpf.length - 1; i++)
                if (cpf.charAt(i) != cpf.charAt(i + 1)) {
                    digitos_iguais = 0;
                    break;
                }
            if (!digitos_iguais) {
                numeros = cpf.substring(0, 9);
                digitos = cpf.substring(9);
                soma = 0;
                for (i = 10; i > 1; i--) {
                    soma += numeros.charAt(10 - i) * i;
                    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
                }
                if (resultado != digitos.charAt(0)) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">O CPF está inválido!</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "O CPF está inválido!");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                }
                numeros = cpf.substring(0, 10);
                soma = 0;
                for (i = 11; i > 1; i--) {
                    soma += numeros.charAt(11 - i) * i;
                    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
                }
                if (resultado != digitos.charAt(1)) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">O CPF está inválido!</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "O CPF está inválido!");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                }
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else $(evt).addClass("is-invalid");
            if (
                $(evt).next("div.invalid-feedback").length == 0 &&
                !$(evt).attr("data-toggle")
            ) {
                $(evt).after(
                    `<div class="invalid-feedback">O CPF está inválido!</div>`
                );
            }
            if ($(evt).attr("data-toggle")) {
                $(evt).attr("data-content", "O CPF está inválido!");
                $(evt).popover();
                $(evt).popover("enable");
            }
            return false;
        },
        valida_campo_vazio(evt, carac_minimo) {
            let valor = $(evt).val();
            let quant = carac_minimo;
            $(evt).siblings("div.invalid-feedback").remove();

            if ((valor.length == 0 || !valor) && quant > 0) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).siblings("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    if ($(evt).siblings("div.input-group-append").length) {
                        $(evt)
                            .siblings("div.input-group-append")
                            .after(`<div class="invalid-feedback">Campo obrigatório</div>`);
                    } else {
                        $(evt).after(
                            `<div class="invalid-feedback">Campo obrigatório</div>`
                        );
                    }
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "Campo obrigatório");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            } else {
                if ((valor.length > 0 || !valor) && valor.length < quant) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).siblings("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        if ($(evt).siblings("div.input-group-append").length) {
                            $(evt)
                                .siblings("div.input-group-append")
                                .after(`<div class="invalid-feedback">Campo obrigatório</div>`);
                        } else {
                            $(evt).after(
                                `<div class="invalid-feedback">Campo obrigatório</div>`
                            );
                        }
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "Campo obrigatório");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                } else {
                    $(evt).removeClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length > 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        //$(evt).next('div.invalid-feedback').remove();
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).popover("disable");
                    }
                    return true;
                }
            }
        },
        valida_campo(evt, carac_minimo) {
            let valor = $(evt).val();
            let quant = carac_minimo;
            $(evt).siblings("div.invalid-feedback").remove();

            if (valor.length == 0 || !valor) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            }

            if ((valor.length > 0 || !valor) && valor.length < quant) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).siblings("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    if ($(evt).siblings("div.input-group-append").length) {
                        $(evt)
                            .siblings("div.input-group-append")
                            .after(`<div class="invalid-feedback">Campo obrigatório</div>`);
                    } else {
                        $(evt).after(
                            `<div class="invalid-feedback">Campo obrigatório</div>`
                        );
                    }
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "Campo obrigatório");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            } else {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            }
        },
        valida_cnpj_vazio(evt) {
            $(evt).next("div.invalid-feedback").remove();

            let cnpj = $(evt).val();
            cnpj = this.replaceAll(cnpj, ".", ""); // tira os pontos
            cnpj = this.replaceAll(cnpj, "-", ""); // tira os pontos
            cnpj = this.replaceAll(cnpj, "/", ""); // tira os pontos
            let soma1, soma2, resto, digito1, digito2;
            if (cnpj.length != 14) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(
                        `<div class="invalid-feedback">O CNPJ está incompleto!</div>`
                    );
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "O CNPJ está incompleto!");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            }
            soma1 =
                cnpj[0] * 5 +
                cnpj[1] * 4 +
                cnpj[2] * 3 +
                cnpj[3] * 2 +
                cnpj[4] * 9 +
                cnpj[5] * 8 +
                cnpj[6] * 7 +
                cnpj[7] * 6 +
                cnpj[8] * 5 +
                cnpj[9] * 4 +
                cnpj[10] * 3 +
                cnpj[11] * 2;
            resto = soma1 % 11;
            digito1 = resto < 2 ? 0 : 11 - resto;

            soma2 =
                cnpj[0] * 6 +
                cnpj[1] * 5 +
                cnpj[2] * 4 +
                cnpj[3] * 3 +
                cnpj[4] * 2 +
                cnpj[5] * 9 +
                cnpj[6] * 8 +
                cnpj[7] * 7 +
                cnpj[8] * 6 +
                cnpj[9] * 5 +
                cnpj[10] * 4 +
                cnpj[11] * 3 +
                cnpj[12] * 2;
            resto = soma2 % 11;
            digito2 = resto < 2 ? 0 : 11 - resto;
            if (cnpj[12] == digito1 && cnpj[13] == digito2) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(`<div class="invalid-feedback">CNPJ inválido!</div>`);
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "CNPJ inválido!");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            }
        },
        valida_data_vazio(evt) {
            $(evt).next("div.invalid-feedback").remove();

            let valor = $(evt).val();

            if (valor.length == 0) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(`<div class="invalid-feedback">Campo obrigatório</div>`);
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "Campo obrigatório");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            } else {
                if (valor.length > 0 && valor.length < 10) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">Data incompleta! Exemplo: dd/mm/aaaa</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "Data incompleta! Exemplo: dd/mm/aaaa");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                } else {
                    let data = moment(valor, "DD/MM/YYYY");
                    if (data.isValid()) {
                        $(evt).removeClass("is-invalid");
                        if (
                            $(evt).next("div.invalid-feedback").length > 0 &&
                            !$(evt).attr("data-toggle")
                        ) {
                            //$(evt).next('div.invalid-feedback').remove();
                        }
                        if ($(evt).attr("data-toggle")) {
                            $(evt).popover("disable");
                        }
                        return true;
                    } else {
                        $(evt).addClass("is-invalid");
                        if (
                            $(evt).next("div.invalid-feedback").length == 0 &&
                            !$(evt).attr("data-toggle")
                        ) {
                            $(evt).after(
                                `<div class="invalid-feedback">Data inválida!</div>`
                            );
                        }
                        if ($(evt).attr("data-toggle")) {
                            $(evt).attr("data-content", "Data inválida! Exemplo: dd/mm/aaaa");
                            $(evt).popover();
                            $(evt).popover("enable");
                        }
                        return false;
                    }
                }
            }
        },
        valida_data(evt) {
            $(evt).next("div.invalid-feedback").remove();

            let valor = $(evt).val();
            if (valor.length == 0) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else {
                if (valor.length > 0 && valor.length < 10) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">Data incompleta! Exemplo: dd/mm/aaaa</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "Data incompleta! Exemplo: dd/mm/aaaa");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                } else {
                    let data = moment(valor, "DD/MM/YYYY");
                    if (data.isValid()) {
                        $(evt).removeClass("is-invalid");
                        if (
                            $(evt).next("div.invalid-feedback").length > 0 &&
                            !$(evt).attr("data-toggle")
                        ) {
                            //$(evt).next('div.invalid-feedback').remove();
                        }
                        if ($(evt).attr("data-toggle")) {
                            $(evt).popover("disable");
                        }
                        return true;
                    } else {
                        $(evt).addClass("is-invalid");
                        if (
                            $(evt).next("div.invalid-feedback").length == 0 &&
                            !$(evt).attr("data-toggle")
                        ) {
                            $(evt).after(
                                `<div class="invalid-feedback">Data inválida!</div>`
                            );
                        }
                        if ($(evt).attr("data-toggle")) {
                            $(evt).attr("data-content", "Data inválida! Exemplo: dd/mm/aaaa");
                            $(evt).popover();
                            $(evt).popover("enable");
                        }
                        return false;
                    }
                }
            }
        },
        valida_cep_vazio(evt) {
            $(evt).siblings("div.invalid-feedback").remove();

            let valor = $(evt).val();
            if (valor.length == 0) {
                $(evt).addClass("is-invalid");

                if (
                    ($(evt).siblings("div.invalid-feedback").length == 0 ||
                        $(evt).siblings("div.input-group-append").length == 0) &&
                    !$(evt).attr("data-toggle")
                ) {
                    if ($(evt).siblings("div.input-group-append").length) {
                        $(evt)
                            .next("div.input-group-append")
                            .eq(0)
                            .after(`<div class="invalid-feedback">Campo obrigatório</div>`);
                    } else {
                        $(evt).after(
                            `<div class="invalid-feedback">Campo obrigatório</div>`
                        );
                    }
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "Campo obrigatório");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            } else {
                if (valor.length > 0 && valor.length < 9) {
                    $(evt).addClass("is-invalid");
                    if (
                        ($(evt).siblings("div.invalid-feedback").length == 0 ||
                            $(evt).siblings("div.input-group-append").length == 0) &&
                        !$(evt).attr("data-toggle")
                    ) {
                        if ($(evt).siblings("div.input-group-append").length) {
                            $(evt)
                                .next("div.input-group-append")
                                .eq(0)
                                .after(
                                    `<div class="invalid-feedback">Exemplo: 65000-000</div>`
                                );
                        } else {
                            $(evt).after(
                                `<div class="invalid-feedback">Exemplo: 65000-000</div>`
                            );
                        }
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "Exemplo: 65000-000");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                } else {
                    $(evt).removeClass("is-invalid");
                    if (
                        ($(evt).siblings("div.invalid-feedback").length > 0 ||
                            $(evt).siblings("div.input-group-append").length > 0) &&
                        !$(evt).attr("data-toggle")
                    ) {
                        //$(evt).next('div.invalid-feedback').remove();
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).popover("disable");
                    }
                    return true;
                }
            }
        },
        validaEmailVazio(evt) {
            $(evt).next("div.invalid-feedback").remove();

            //var regex=/^[\w.-_\+]+@[\w-]+(\.\w{2,4})+$/;
            var regex =
                /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            var valor = $(evt).val();

            if (regex.test(valor)) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(`<div class="invalid-feedback">E-mail inválido</div>`);
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "E-mail inválido");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            }
        },
        validaEmail(evt) {
            $(evt).next("div.invalid-feedback").remove();

            //var regex=/^[\w.-_\+]+@[\w-]+(\.\w{2,4})+$/;
            var regex =
                /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            var valor = $(evt).val();

            if (regex.test(valor)) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else {
                if (valor.length == 0) {
                    $(evt).removeClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length > 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        //$(evt).next('div.invalid-feedback').remove();
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).popover("disable");
                    }
                    return true;
                } else {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(`<div class="invalid-feedback">E-mail inválido</div>`);
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr("data-content", "E-mail inválido");
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                }
            }
        },
        mascaraTelefone() {
            $(".telefone, .telefone9").each(function (index, element) {
                var telefone = $(this).val();
                telefone = this.replaceAll(telefone, "(", "");
                telefone = this.replaceAll(telefone, ")", "");
                telefone = this.replaceAll(telefone, " ", "");
                telefone = this.replaceAll(telefone, "-", "");

                $(element).removeClass("telefone");
                $(element).removeClass("telefone9");

                if (telefone.length == 11) {
                    $(element).setMask("phone9");
                    $(element).addClass("telefone9");
                } else {
                    $(element).setMask("phone");
                    $(element).addClass("telefone");
                }
            });
        },
        valida_telefone_vazio(evt) {
            $(evt).next("div.invalid-feedback").remove();

            var valor = $(evt).val();

            valor = this.replaceAll(valor, "(", "");
            valor = this.replaceAll(valor, ")", "");
            valor = this.replaceAll(valor, " ", "");
            valor = this.replaceAll(valor, "-", "");

            if (valor.length == 0) {
                $(evt).addClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length == 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    $(evt).after(`<div class="invalid-feedback">Campo obrigatório</div>`);
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).attr("data-content", "Campo obrigatório");
                    $(evt).popover();
                    $(evt).popover("enable");
                }
                return false;
            } else {
                if (valor.length > 0 && valor.length < 10) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">Telefone incompleto! Exemplo: (98) 3235-5010</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr(
                            "data-content",
                            "Telefone incompleto! Exemplo: (98) 3235-5010"
                        );
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                } else {
                    $(evt).removeClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length > 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        //$(evt).next('div.invalid-feedback').remove();
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).popover("disable");
                    }
                    return true;
                }
            }
        },
        valida_telefone(evt) {
            // funcao base de validar telefone

            $(evt).next("div.invalid-feedback").remove();

            var valor = $(evt).val();
            valor = this.replaceAll(valor, "(", "");
            valor = this.replaceAll(valor, ")", "");
            valor = this.replaceAll(valor, " ", "");
            valor = this.replaceAll(valor, "-", "");

            if (valor.length == 0) {
                $(evt).removeClass("is-invalid");
                if (
                    $(evt).next("div.invalid-feedback").length > 0 &&
                    !$(evt).attr("data-toggle")
                ) {
                    //$(evt).next('div.invalid-feedback').remove();
                }
                if ($(evt).attr("data-toggle")) {
                    $(evt).popover("disable");
                }
                return true;
            } else {
                if (valor.length > 0 && valor.length < 10) {
                    $(evt).addClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length == 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        $(evt).after(
                            `<div class="invalid-feedback">Telefone incompleto! Exemplo: (98) 3235-5010</div>`
                        );
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).attr(
                            "data-content",
                            "Telefone incompleto! Exemplo: (98) 3235-5010"
                        );
                        $(evt).popover();
                        $(evt).popover("enable");
                    }
                    return false;
                } else {
                    $(evt).removeClass("is-invalid");
                    if (
                        $(evt).next("div.invalid-feedback").length > 0 &&
                        !$(evt).attr("data-toggle")
                    ) {
                        //$(evt).next('div.invalid-feedback').remove();
                    }
                    if ($(evt).attr("data-toggle")) {
                        $(evt).popover("disable");
                    }
                    return true;
                }
            }
        },
    },
};

export default validacoes;
