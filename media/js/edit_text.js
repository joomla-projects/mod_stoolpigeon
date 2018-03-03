window.addEvent('domready', function()
{
	//find the editable areas
	$$('.editable').each(function(el_diff_tags)
	{



			//add double-click and blur events
			el_diff_tags.addEvent('dblclick',function()
			{
			var create_cookie_field = '0';
			var delete_cookie_field = '0';
			var false_positive = '0';
			var original = el_diff_tags.get('rel_original');
			var catched = el_diff_tags.get('rel_catched');
			var rel_rows = el_diff_tags.get('rel_rows');
			var rel_id = el_diff_tags.get('rel_id');
			var rel_zone = el_diff_tags.get('rel_zone');
			var rel_file = el_diff_tags.get('rel_file');
			var rel_key = el_diff_tags.get('rel_key');
			var id_ref = unescape("tag_" + rel_id + "_" + rel_zone + "_" + rel_file + "_" + rel_key);
			var id_ref_b = unescape("text_" + rel_id + "_" + rel_zone + "_" + rel_file + "_" + rel_key);
 
				if(el_diff_tags.hasClass('editable'))
				{ 

				el_diff_tags.removeClass('editable')
 
				//store "before" message
				var before = el_diff_tags.get('text').trim();

				//erase current
				el_diff_tags.set('html','');

				//replace current text/content with input element (no multi-line available)
				var input = new Element('input', { 'class':'box', 'value':before });
				//blur input when they press "Enter"
					input.addEvent('keydown', function(e)
					{
					if(e.key == 'enter') { this.fireEvent('blur'); };
					if(e.key == 'esc') { this.fireEvent('escape'); }
					});
				
				input.inject(el_diff_tags).select();
 
				//add escape event to input
				input.addEvent('escape', function()
				{
				val = input.get('value').trim();
				el_diff_tags.set('text',before).addClass(val != '' ? '' : 'editable-empty');   //empty field enable editable-empty class
				el_diff_tags.addClass('editable');
				});
 
				//add blur event to input
				input.addEvent('blur', function()
				{
				//get value, place it in original element
				val = input.get('value').trim();
				el_diff_tags.set('text',val).addClass(val != '' ? '' : 'editable-empty');

					if(el_diff_tags.get('text') != '' && el_diff_tags.get('text') != before && el_diff_tags.get('text') != original)
					{
					//the text is not empty or text has been changed after edit.. ok, wellcome to the cookies matrix.
					document.getElementById(id_ref).innerHTML = 'THE NEW VALUE CHANGED HAS BEEN STORED';
						if (rel_rows == '6')
						{
						document.getElementById(id_ref_b).innerHTML = el_diff_tags.get('text');
						}

					var value = el_diff_tags.get('text').trim();
					el_diff_tags.set('text',value).addClass('editable');

					var create_cookie_field = '1';
					var delete_cookie_field = '0';
					var false_positive = '0';


					}else if (el_diff_tags.get('text') == ''){
					//the text is empty... replace it with the original value and make it editable again.

					document.getElementById(id_ref).innerHTML = 'FIELD EMPTY: RESTORED TO THE ORIGINAL VALUE';
						if (rel_rows == '6')
						{
						document.getElementById(id_ref_b).innerHTML = original;
						}

					var value = el_diff_tags.get('rel_original').trim();
					//single quotes issue breaking code when is passed from PHP is solved here, replacing again to single quote again.
					value = replaceAll(value, "MSP_SINGLE_QUOTES", "\'" );

					el_diff_tags.set('text',value).addClass('editable');

					var create_cookie_field = '0';
					var delete_cookie_field = '1';
					var false_positive = '0';


					}else if (el_diff_tags.get('text') == original ){
					//false positive mistakes. IE, source and target means the same and we change target text by mistake.
					//Editing target and pressing enter when the EDITED VALUE STORED FOUNDED AFTER REFRESH is present,
					//restore the original source text.

					document.getElementById(id_ref).innerHTML = 'INITIAL STATE is BACK: UNTRANSLATED';
						if (rel_rows == '6')
						{
						document.getElementById(id_ref_b).innerHTML = original;
						}

					var value = el_diff_tags.get('rel_original').trim();
					el_diff_tags.set('text',value).addClass('editable');
					
					var create_cookie_field = '0';
					var delete_cookie_field = '1';
					var false_positive = '1';
					}

		
				var id = el_diff_tags.get('rel_id');
				var zone = el_diff_tags.get('rel_zone');
				var file = el_diff_tags.get('rel_file');
				var key = el_diff_tags.get('rel_key');


 
				//save/delete cookies from matrix if needed.
					if (create_cookie_field == '1' && false_positive == '0')
					{
					var myCookie = Cookie.write('changes_diff_tags', '1', {path: <?echo $joomla_path;?>});
					var myCookie = Cookie.write("diff_tags[" + id + "][" + zone + "][" + file + "][" + key  + "]", value, {path: <?echo $joomla_path;?>});
					} else if (delete_cookie_field == '1' && false_positive == '1') {

					var cookie_name = 'diff_tags[' + id + '][' + zone + '][' + file + '][' + key  + ']';

					var myCookie = Cookie.write(cookie_name, before, {duration: -1, path: <?echo $joomla_path;?>});
					var myCookie = Cookie.write(cookie_name, value, {duration: -1, path: <?echo $joomla_path;?>});

					} else if (delete_cookie_field == '1' && false_positive == '0') {
					var cookie_name = 'diff_tags[' + id + '][' + zone + '][' + file + '][' + key  + ']';

					var myCookie = Cookie.write(cookie_name, value, {duration: -1, path: <?echo $joomla_path;?>});

					} 		

				el_diff_tags.addClass('editable');

				});
 
			}; //End dblclick
		});
	});


});

function replaceAll( text, busca, reemplaza ){
  while (text.toString().indexOf(busca) != -1)
      text = text.toString().replace(busca,reemplaza);
  return text;
}
