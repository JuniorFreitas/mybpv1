#!/usr/bin/env node
/**
 * Conversor de CSV para JSON em JavaScript/Node.js
 * Converte o arquivo nr35_csv.csv para formato JSON
 */

const fs = require('fs')
const path = require('path')

/**
 * Converte arquivo CSV para JSON
 * @param {string} csvFilePath - Caminho para o arquivo CSV
 * @param {string} jsonFilePath - Caminho para o arquivo JSON de saída
 * @param {boolean} compact - Se deve gerar JSON compacto
 */
function csvToJson(csvFilePath, jsonFilePath, compact = false) {
    try {
        // Lê o arquivo CSV
        const csvContent = fs.readFileSync(csvFilePath, 'utf8')

        // Divide em linhas
        const lines = csvContent.split('\n').filter(line => line.trim())

        // Obtém os cabeçalhos da primeira linha
        const headers = lines[0].split(';')

        // Processa as linhas de dados
        const data = []

        for (let i = 1; i < lines.length; i++) {
            const values = lines[i].split(';')

            // Cria objeto para cada funcionário
            const funcionario = {}

            headers.forEach((header, index) => {
                if (values[index]) {
                    funcionario[header.trim()] = values[index].trim()
                }
            })

            // Só adiciona se tiver dados válidos
            if (Object.keys(funcionario).length > 0) {
                data.push(funcionario)
            }
        }

        // Salva o arquivo JSON
        const jsonContent = compact
            ? JSON.stringify(data)
            : JSON.stringify(data, null, 2)

        fs.writeFileSync(jsonFilePath, jsonContent, 'utf8')

        console.log(`✅ Conversão concluída com sucesso!`)
        console.log(`📊 Total de registros convertidos: ${data.length}`)
        console.log(`📁 Arquivo JSON salvo em: ${jsonFilePath}`)

        return true

    } catch (error) {
        console.error(`❌ Erro durante a conversão: ${error.message}`)
        return false
    }
}

/**
 * Função principal
 */
function main() {
    // Obtém o nome do arquivo CSV dos argumentos da linha de comando
    const args = process.argv.slice(2)

    if (args.length === 0) {
        console.log('📖 Uso: node csv_to_json.js <arquivo_csv> [arquivo_json_saida]')
        console.log('💡 Exemplo: node csv_to_json.js dados.csv saida.json')
        console.log('💡 Se não especificar o arquivo de saída, será usado o nome do CSV + .json')
        return
    }

    const csvFile = args[0]
    const jsonFile = args[1] || csvFile.replace(/\.csv$/i, '.json')
    const jsonCompactFile = jsonFile.replace(/\.json$/i, '_compact.json')

    console.log('🔄 Iniciando conversão de CSV para JSON...')
    console.log(`📂 Arquivo CSV: ${csvFile}`)
    console.log(`📁 Arquivo JSON de saída: ${jsonFile}`)
    console.log(`📁 Arquivo JSON compacto: ${jsonCompactFile}`)

    // Verifica se o arquivo CSV existe
    if (!fs.existsSync(csvFile)) {
        console.error(`❌ Arquivo ${csvFile} não encontrado!`)
        console.log('💡 Verifique se o arquivo existe e o caminho está correto')
        return
    }

    // Converte para JSON formatado
    console.log('\n📝 Convertendo para JSON formatado...')
    if (csvToJson(csvFile, jsonFile, false)) {
        // Converte para JSON compacto
        console.log('\n📝 Convertendo para JSON compacto...')
        csvToJson(csvFile, jsonCompactFile, true)
    }

    console.log('\n🎉 Processo finalizado!')
}

// Executa se for chamado diretamente
if (require.main === module) {
    main()
}

module.exports = { csvToJson }
