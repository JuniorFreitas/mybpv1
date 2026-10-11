// Extrai o texto de cada pagina do PDF em JSON (usado para numerar o sumario).
import Foundation
import PDFKit
let a = CommandLine.arguments
let doc = PDFDocument(url: URL(fileURLWithPath: a[1]))!
var textos: [String] = []
for i in 0..<doc.pageCount { textos.append(doc.page(at: i)?.string ?? "") }
try! JSONSerialization.data(withJSONObject: textos).write(to: URL(fileURLWithPath: a[2]))
