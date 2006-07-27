// a very simple function to add a row at the bottom of a table...

function add_row(a_element) 
{
    var element;   
    
//        dbg = document.getElementById("aegir_msg");
//        dbg.style.display = "block";
		
		var row = a_element.parentNode.parentNode.cloneNode(true);
		var table = a_element.parentNode.parentNode.parentNode;		
		var td;
		var name;
		var input;
		var rownum;
		var re = /datamanager_table_rows[1][2]/;
		for (i = 0 ; i < row.childNodes.length;i++) 
		{
			td = row.childNodes[i];

			if (td.firstChild && td.firstChild.tagName == 'INPUT') 
			{
				input = td.firstChild;
				name = td.firstChild.getAttribute("name");
				rownum = name.substring(23,24);
				rownum++;
				name   = name.substring(0,23) + rownum + name.substring(24,29);
				input.setAttribute('name', name);
//				dbg.innerHTML += "<br/> Newname: " + name ;
//			dbg.innerHTML +=  "  oldname: :" + td.firstChild.getAttribute("name") + "";
//			dbg.innerHTML +=  "" + i + ":" + td.firstChild.id + "<br/>";
			}
				
			
		}
		a_element.parentNode.parentNode.removeChild(a_element.parentNode);
		table.appendChild(row);
}
