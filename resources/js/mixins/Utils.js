import tinymceSelfhost from '../utils/tinymceSelfhost'

const selfhost = tinymceSelfhost && tinymceSelfhost.getTinyMceInit ? tinymceSelfhost : (tinymceSelfhost && tinymceSelfhost.default) || tinymceSelfhost
const { getTinyMceInit } = selfhost

const Utils = {
    computed: {
        urlSite() {
            return process.env.MIX_URL_SITE;
        },
        ufs() {
            return ["AC", "AL", "AP", "AM", "BA", "CE", "DF", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO"];
        },
        estados() {
            return ["Acre", "Alagoas", "Amapá", "Amazonas", "Bahia", "Ceará", "Distrito Federal", "Espírito Santo", "Goiás", "Maranhão", "Mato Grosso", "Mato Grosso do Sul", "Minas Gerais", "Pará", "Paraíba", "Paraná", "Pernambuco", "Piauí", "Rio de Janeiro", "Rio Grande do Norte", "Rio Grande do Sul", "Rondônia", "Roraima", "Santa Catarina", "São Paulo", "Sergipe", "Tocantins"];
        },
        por_pagina() {
            return [20, 50, 100, 150];
        },
        tinySimples() {
            return getTinyMceInit('simples')
        }
    },
    methods: {
        generateUuid() {
            // Create 32 random hexadecimal characters (0-9, a-f) with dashes in between
            const randomHex = () => Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);

            // Create the UUID with specific sections: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
            return (
                randomHex() + randomHex() + // 8 characters
                '-' +
                randomHex() + // 4 characters
                '-' +
                '4' + randomHex().substring(0, 3) + // 13th character is '4' (UUID version 4)
                '-' +
                ((8 + Math.floor(Math.random() * 4)).toString(16)) + randomHex().substring(0, 3) + // 17th character is '8', '9', 'A', or 'B'
                '-' +
                randomHex() + randomHex() + randomHex() // 12 characters
            );
        }
    }
};

export default Utils;
