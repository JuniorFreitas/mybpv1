export default function (value) {
    if(value!=null){
        while (value.indexOf("\n") !== -1) {
            value = value.replace("\n", '<br/>');
        }
        return value;
    }
    return value;

}
