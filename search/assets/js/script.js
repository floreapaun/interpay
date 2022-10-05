function search(){
    "use strict";   
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("POST", "search.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.onreadystatechange = function() {
        if (this.readyState === 4 || this.status === 200){ 
            document.getElementById('search_result').innerHTML = this.responseText;
            $("table tr").hide();
            $("table tr").each(function(index){
                $(this).delay(index*4500).show(1000);
            });
        }       
    };
    xmlhttp.send("query=" + document.getElementById('search_text').value);
}
