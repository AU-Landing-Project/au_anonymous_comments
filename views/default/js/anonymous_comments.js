/**
 * Function : dump() Arguments: The data - array,hash(associative array),object
 * The level - OPTIONAL Returns : The textual representation of the array. This
 * function was inspired by the print_r function of PHP. This will accept some
 * data as the argument and return a text that will be a more readable version
 * of the array/hash/object that is given. Docs:
 * http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 * 
 * 	Left in for debugging - Matt
 */
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	// The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { // Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { // If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { // Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}


// fix for IE stupidity

if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(value, start) {
		var i;
		if (!start) {
			start = 0;
		}
		for(i=start; i<this.length; i++) {
			if(this[i] == value) {
				return i;
			}
		}
	return -1;
	}
}


function mc_toggle_approve(comment_id){
	var mctoggle = 0;
	var length = idarray.length;
	
	// see if our comment is in the array or not
    for(var i = 0; i < length; i++) {
        if(idarray[i] == comment_id){
        	mctoggle = 1;
        }
    }
    
    if(mctoggle == 1){
    	// need to remove from array
    	var idx = idarray.indexOf(comment_id); // Find the index
    	if(idx!=-1) idarray.splice(idx, 1);
    }
    else{
    	// need to add to array
    	idarray.push(comment_id);
    }
    
    //now we assign the new value to the input
	document.getElementById("mcApprovalID").value = idarray;
	document.getElementById("mcDeleteID").value = idarray;
}

alert('test');