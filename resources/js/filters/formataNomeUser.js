export default function (value) {
    let partes = value.split(" ");
    if(partes.length === 1){
        if(partes[0].length===1){
            return partes[0][0];
        }else{
            return partes[0][0]+partes[0][1];
        }

    }
    if(partes.length > 1){
        return partes[0][0] + partes[1][0];
    }
}
