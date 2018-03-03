<?php
/**
 * mod_stoolpigeon_functions.php
 * Copyright (C) 2011-2012 www.comunidadjoomla.org. All rights reserved.
 * GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


function synchronise_target(&$matrix_info = array(), $client = '', &$client_data = array())
{
	$state = '';
	$slt   = $matrix_info['config']['source_language_tag'];
	$tlt   = $matrix_info['config']['target_language_tag'];
	$sv    = $matrix_info['config']['version_options']['source_version'];
	$tv    = $matrix_info['config']['version_options']['target_version'];

	if ($client == 'admin')
	{
		$source_path = $matrix_info['config']['paths']['saf'];
		$target_path = $matrix_info['config']['paths']['taf'];

	}
	elseif ($client == 'site')
	{

		$source_path = $matrix_info['config']['paths']['ssf'];
		$target_path = $matrix_info['config']['paths']['tsf'];

	}
	elseif ($client == 'installation')
	{

		$source_path = $matrix_info['config']['paths']['sif'];
		$target_path = $matrix_info['config']['paths']['tif'];
	}

	$display_package_link = $matrix_info['config']['experimental_options']['display_package_link'];
	$new_base_path        = $client_data['new_base_path'];

	$info         = array();
	$files_to_zip = $client_data['files_to_zip'];


	if (!JFolder::create($new_base_path))
	{
	}
	if (!JFolder::create($new_base_path . '/' . $client))
	{
	}


	//Mandatory inexistent files in target are addeds with renamed target language tag.

	if (!empty($matrix_info['incidences']['file_non_existent'][$client . '_ini_files']['target']))
	{
		$missed_target_files[$client] = $matrix_info['incidences']['file_non_existent'][$client . '_ini_files']['target'];

		if (!empty($matrix_info['incidences']['file_non_existent'][$client . '_ini_files']['source']))
		{
			$missed_source_files[$client] = $matrix_info['incidences']['file_non_existent'][$client . '_ini_files']['source'];
		}
		else
		{
			$missed_source_files[$client] = array();
		}

		foreach ($missed_target_files[$client] as $file)
		{
			$source_file          = $source_path . '/' . $slt . '.' . $file;
			$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file;

			if (!in_array($file, $missed_source_files[$client]))
			{
				JFile::copy($source_file, $new_target_file_path);

				if ($matrix_info['config']['experimental_options']['report_files_keys_addeds_or_deleteds'] == '1')
				{
					echo "<br /><font color='green'>" . JText::_('MOD_STOOLPIGEON_ADDED')
						. "</font> | " . strtoupper($client) . " | <font color='red'>MISSED FILE: </font>" . $tlt . '.' . $file . "<br />";
				}

				$files_to_zip .= $new_target_file_path . ",";
			}
			else
			{
				//if any ini file required in target is not present at source, the package begin with "incomplete_".
				$state = 'incomplete_';

				echo "<br />" . JText::_('MOD_STOOLPIGEON_WIP') . "<b>" . $slt . "." . $file . "</b>"
					. JText::_('MOD_STOOLPIGEON_WIP_IS_NOT_PRESENT');
			}
			unset($file);
		}
	}
	//keys to add or delete only can to be present in comparable files. If same file have keys to add and delete... no problem.
	//The file is writted at target location when both "to add and to delete" goals are finished.
	// if no keys to add or delete within particular file, the file is stored at tmp dir too.

	if (isset($matrix_info['incidences']['key_non_existent_in_target'][$client]))
	{
		$files_with_keys_to_add = $matrix_info['incidences']['key_non_existent_in_target'][$client];
	}
	else
	{
		$files_with_keys_to_add = array();
	}
	if (isset($matrix_info['incidences']['key_non_existent_in_source'][$client]))
	{
		$files_with_keys_to_delete = $matrix_info['incidences']['key_non_existent_in_source'][$client];
	}
	else
	{
		$files_with_keys_to_delete = array();
	}

	if (isset($matrix_info['incidences']['keys_to_keep_in_target'][$client]))
	{
		$files_with_keys_to_keep_in_target = $matrix_info['incidences']['keys_to_keep_in_target'][$client];
	}
	else
	{
		$files_with_keys_to_keep_in_target = array();
	}


	$zone_with_files_comparables = $client . "_comparables";

	foreach ($matrix_info[$zone_with_files_comparables] as $file_comparable)
	{


		//LANGUAGE SECTIONS BEGIN

		$file_with_sections_to_add    = array();
		$file_with_sections_to_delete = array();

		if (isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable])
			&& isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
		{

			$sections_to_delete = array_diff($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable],
				$matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable]);

			$sections_to_add = array_diff($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable],
				$matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]);

			if (empty($sections_to_add) && empty($sections_to_delete))
			{
				$file_with_sections_to_add    = array();
				$file_with_sections_to_delete = array();

			}
			else
			{

				if (!empty($sections_to_delete))
				{
					$file_with_sections_to_delete = $sections_to_delete;
				}

				if (!empty($sections_to_add))
				{
					$file_with_sections_to_add = $sections_to_add;
				}

			}


		}
		elseif (!isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable])
			&& !isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
		{

			$file_with_sections_to_add    = array();
			$file_with_sections_to_delete = array();

		}
		elseif (!isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable])
			|| !isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
		{


			if (!empty($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
			{
				$file_with_sections_to_delete = $matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable];
			}

			if (!empty($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable]))
			{
				$file_with_sections_to_add = $matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable];
			}

		}//LANGUAGE SECTIONS END


		if (array_key_exists($file_comparable, $files_with_keys_to_delete)
			|| array_key_exists($file_comparable, $files_with_keys_to_add)
			|| array_key_exists($file_comparable, $files_with_keys_to_keep_in_target)
			|| !empty($file_with_sections_to_add)
			|| !empty($file_with_sections_to_delete))
		{

			$client_synchroniseds_to_delete_keys = array();
			$client_synchroniseds_to_add_keys    = array();
			$client_synchroniseds_to_keep_keys   = array();
			$tmp_content[$client]                = array();

			//To delete block

			if (isset($files_with_keys_to_delete)
				&& array_key_exists($file_comparable, $files_with_keys_to_delete))
			{

				if (!array_key_exists($client, $client_synchroniseds_to_delete_keys)
					|| !isset($client_synchroniseds_to_delete_keys))
				{
					$client_synchroniseds_to_delete_keys[$client] = 'CATCHED';
					$file_synchroniseds_to_delete_keys            = array();

					if (!array_key_exists($file_comparable, $file_synchroniseds_to_delete_keys)
						|| !isset($file_synchroniseds_to_delete_keys))
					{
						$keys_synchroniseds_to_delete = array();
						$keys_inserteds_dm            = array();

						$new_file_content = '';

						$target_file_path = $target_path . '/' . $tlt . '.' . $file_comparable;

						$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;

						$catched_file_content = JFile::read($target_file_path);
						//replaced due seems this one can to fail with Mac or Windows EOL format
						//$catched_file_content_lines = explode("\n", $catched_file_content);
						$catched_file_content_lines = preg_split('/\r\n|\r|\n/', $catched_file_content);


						foreach ($catched_file_content_lines as $line)
						{
							$file_synchroniseds_to_delete_keys[$file_comparable] = 'OPENED';
							//to delete protocol
							$trimmed_line = trim($line);

							if ((empty($line)) || ($line{0} == '#') || ($line{0} == ';') || ($trimmed_line{0} == '['))
							{
								$new_file_content .= $line . "\n";

							}
							else
							{

								list($target_key, $old_value) = explode('=', $line, 2);

								if (in_array($target_key, $files_with_keys_to_delete[$file_comparable])
									&&
									(
										!array_key_exists($target_key, $keys_synchroniseds_to_delete)
										||
										!isset($keys_synchroniseds_to_delete)
									))
								{
									//Do nothing :D and the key to delete is not added.
									$keys_synchroniseds_to_delete[$target_key] = 'DELETED';//for my control

									if ($matrix_info['config']['experimental_options']
										['report_files_keys_addeds_or_deleteds'] == '1')
									{
										echo "<br /><font color='red'>" . JText::_('MOD_STOOLPIGEON_DELETED')
											. "</font>" . JText::_('MOD_STOOLPIGEON_' . strtoupper($client) . '_MISSED_KEY')
											. $tlt . '.' . $file_comparable
											. JText::_('MOD_STOOLPIGEON_SANITIZED_KEY') . $target_key . "<br />";
									}


								}
								elseif (!in_array($target_key, $files_with_keys_to_delete[$file_comparable])
									&&
									(
										!array_key_exists($target_key, $keys_inserteds_dm)
										||
										!isset($keys_inserteds_dm)
									))
								{
									$new_file_content               .= $line . "\n";
									$keys_inserteds_dm[$target_key] = 'INSERTED';//for my control

									//echo "<br />Insertada: " . $target_key . " from $file_comparable";
								}
							}

							unset($line);
						}
						$tmp_content[$client][$file_comparable] = trim($new_file_content);

					}//end if file OPENED
				}

			}

			//To add block

			if (isset($files_with_keys_to_add) && array_key_exists($file_comparable, $files_with_keys_to_add))
			{

				if (!array_key_exists($client, $client_synchroniseds_to_add_keys) || !isset($client_synchroniseds_to_add_keys))
				{
					$client_synchroniseds_to_add_keys[$client] = 'CATCHED';
					$file_synchroniseds_to_add_keys            = array();


					if (!array_key_exists($file_comparable, $file_synchroniseds_to_add_keys) || !isset($file_synchroniseds_to_add_keys))
					{

						$catch                     = get_target_lines($info, $matrix_info, $client, $files_with_keys_to_add, $file_comparable);
						$keys_synchroniseds_to_add = array();
						$keys_inserteds_am         = array();
						$new_file_content          = '';

						$target_file_path = $target_path . '/' . $tlt . '.' . $file_comparable;

						$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;

						if (array_key_exists($file_comparable, $tmp_content[$client]))
						{
							//if we have already opened file for delete keys, we take this one for begin to add the new ones.
							$catched_file_content = $tmp_content[$client][$file_comparable];
						}
						else
						{
							//if not opened previously for delete keys, we take the target file content for begin to add the new ones.
							$catched_file_content = JFile::read($target_file_path);
						}

						//replaced due seems this one can to fail with Mac or Windows EOL format
						//$catched_file_content_lines = explode("\n", $catched_file_content);
						$catched_file_content_lines = preg_split('/\r\n|\r|\n/', $catched_file_content);


						foreach ($info[$client][$file_comparable]['target_line'] as $line_to_add_id => $key)
						{

							$source_line = $info[$client][$file_comparable]['source_line'][$line_to_add_id];
							//echo "<br />Source line: " . $source_line . " Line to add id: " . $line_to_add_id;

							//workarround to add elements in any position of the array
							$line_to_catch = $line_to_add_id - 1;

							$before = $catched_file_content_lines[$line_to_catch];

							$catched_file_content_lines[$line_to_catch] = $source_line . "\n" . $before;

							//workarround to sort new entries every time and avoid wrong results
							// due we are adding blocks of lines following a numeric order (15, 16, 17..)

							$reordenator = implode("\n", $catched_file_content_lines);

							$catched_file_content_lines = explode("\n", $reordenator);

							// and tarari taratiiii... when finish to add all, we leave the new ones in same order than in source file :D
							if ($matrix_info['config']['experimental_options']
								['report_files_keys_addeds_or_deleteds'] == '1')
							{
								echo "<br /><font color='green'>" . JText::_('MOD_STOOLPIGEON_ADDED')
									. "</font>" . JText::_('MOD_STOOLPIGEON_' . strtoupper($client)
										. '_MISSED_KEY') . $tlt . '.' . $file_comparable
									. JText::_('MOD_STOOLPIGEON_SANITIZED_KEY')
									. $key . JText::_('MOD_STOOLPIGEON_LINE') . $line_to_add_id . "<br />";
							}
							unset ($line_to_add_id, $key);
						}

						$new_file_content                       = implode("\n", $catched_file_content_lines);
						$tmp_content[$client][$file_comparable] = trim($new_file_content);
					}
				}
			}


			if (!empty($file_with_sections_to_delete))
			{
				$new_file_content = '';
				$target_file_path = $target_path . '/' . $tlt . '.' . $file_comparable;

				$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;

				if (array_key_exists($file_comparable, $tmp_content[$client]))
				{
					//if we have already opened file for delete keys, we take this one for begin to add the new ones.
					$catched_file_content = $tmp_content[$client][$file_comparable];
				}
				else
				{
					//if not opened previously for delete keys, we take the target file content for begin to add the new ones.
					$catched_file_content = JFile::read($target_file_path);
				}

				//$catched_file_content_lines = preg_split( '/\r\n|\r|\n/', $catched_file_content );
				$catched_file_content_lines = preg_split('/\r\n|\r|\n/', $catched_file_content);

				foreach ($file_with_sections_to_delete as $section_to_delete_line_number => $section_value_to_delete)
				{


					if (in_array($section_value_to_delete, $catched_file_content_lines))
					{
						$key_id = array_search($section_value_to_delete, $catched_file_content_lines);
						unset($catched_file_content_lines[$key_id]);
					}

				}

				$reordenator = implode("\n", $catched_file_content_lines);


				//$new_file_content = implode("\n", $reordenator);
				$tmp_content[$client][$file_comparable] = trim($reordenator);
				//echo ($tmp_content[$client][$file_comparable]);


			}


			if (!empty($file_with_sections_to_add))
			{
				$new_file_content = '';
				$target_file_path = $target_path . '/' . $tlt . '.' . $file_comparable;

				$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;

				if (array_key_exists($file_comparable, $tmp_content[$client]))
				{
					//if we have already opened file for delete keys, we take this one for begin to add the new ones.
					$catched_file_content = $tmp_content[$client][$file_comparable];
				}
				else
				{
					//if not opened previously for delete keys, we take the target file content for begin to add the new ones.
					$catched_file_content = JFile::read($target_file_path);
				}

				//$catched_file_content_lines = preg_split( '/\r\n|\r|\n/', $catched_file_content );
				$catched_file_content_lines = preg_split('/\r\n|\r|\n/', $catched_file_content);

				foreach ($file_with_sections_to_add as $section_to_add_line_number => $section_value_to_add)
				{

					if (!in_array($section_value_to_add, $catched_file_content_lines))
					{
						$header_lines_source = $matrix_info['file_existent']
						[$client . '_ini_files']['source']['files_info'][$file_comparable]['counter']['first_key'];

						$header_lines_target = $matrix_info['file_existent']
						[$client . '_ini_files']['target']['files_info'][$file_comparable]['counter']['first_key'];

						$target_line   = (($header_lines_target - $header_lines_source) - 1) + ($section_to_add_line_number - 1);
						$previous_line = $target_line;

						$previous                                   = $catched_file_content_lines[$previous_line] . "\n" . $section_value_to_add;
						$catched_file_content_lines[$previous_line] = $previous;
						$reordenator                                = implode("\n", $catched_file_content_lines);
						$catched_file_content_lines                 = preg_split('/\r\n|\r|\n/', $reordenator);
					}
				}

				$reordenator = implode("\n", $catched_file_content_lines);


				//$new_file_content = implode("\n", $reordenator);
				$tmp_content[$client][$file_comparable] = trim($reordenator);
				//echo ($tmp_content[$client][$file_comparable]);


			}


			if (isset($files_with_keys_to_keep_in_target) && array_key_exists($file_comparable, $files_with_keys_to_keep_in_target))
			{
				$new_file_content = '';
				$target_file_path = $target_path . '/' . $tlt . '.' . $file_comparable;

				$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;

				if (array_key_exists($file_comparable, $tmp_content[$client]))
				{
					//if we have already opened file for delete keys, we take this one for begin to add the new ones.
					$catched_file_content = $tmp_content[$client][$file_comparable];
				}
				else
				{
					//if not opened previously for delete keys, we take the target file content for begin to add the new ones.
					$catched_file_content = JFile::read($target_file_path);
				}
				//$catched_file_content_lines = preg_split( '/\r\n|\r|\n/', $catched_file_content );
				$catched_file_content_lines = preg_split('/\r\n|\r|\n/', $catched_file_content);

				foreach ($files_with_keys_to_keep_in_target[$file_comparable] as $key_to_keep => $value_to_keep)
				{
					if (!in_array($key_to_keep . "=" . $value_to_keep, $catched_file_content_lines))
					{
						$catched_file_content_lines[] = $key_to_keep . "=" . $value_to_keep;
						//echo "key to keep: " . $key_to_keep . " Text: " . $value_to_keep . "<br />";
					}
				}

				$reordenator = implode("\n", $catched_file_content_lines);


				//$new_file_content = implode("\n", $reordenator);
				$tmp_content[$client][$file_comparable] = trim($reordenator);
				//echo ($tmp_content[$client][$file_comparable]);

			}

			//writting all the comparable files with keys addeds or deleteds to the tmp directory
			if (isset($tmp_content[$client]))
			{
				foreach ($tmp_content[$client] as $file => $content)
				{
					$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file;
					$trimmedcontent       = trim($content);
					JFile::write($new_target_file_path, $trimmedcontent);
					$files_to_zip .= $new_target_file_path . ",";
					//echo "<br /> Archivo de destino $file copiado al directorio tmp (con algo que añadir o borrar)";
					unset ($file, $content);
				}

			}

		}
		else
		{
			//writing all the comparable files without keys to add or delete to the tmp directory
			$target_file          = $target_path . '/' . $tlt . '.' . $file_comparable;
			$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;
			JFile::copy($target_file, $new_target_file_path);
			$files_to_zip .= $new_target_file_path . ",";

			//echo "<br /> Archivo de destino $file_comparable copiado al directorio tmp (sin nada que añadir o borrar)";
		}
		unset($file_comparable);
	}//end comparables foreach
	if ($files_to_zip != '')
	{
		$client_data['files_to_zip'] = $files_to_zip;
		create_files_pack($matrix_info, $client, $client_data);
	}
	else
	{
		echo "<br /> " . JText::_('MOD_STOOLPIGEON_WITHOUT_FILES_TO_SEND') . $client;
	}

}//end function

function get_target_lines(&$info = array(), &$matrix_info = array(), $client = '', $files_with_keys_to_add = array(), $file_comparable = '')
{

	foreach ($files_with_keys_to_add[$file_comparable] as $key)
	{
		$source_line         = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info'][$file_comparable]['counter']['lines'][$key];
		$source_text         = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info'][$file_comparable]['keys'][$key];
		$header_lines_source = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info'][$file_comparable]['counter']['first_key'];
		$header_lines_target = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info'][$file_comparable]['counter']['first_key'];

		$target_line    = ($header_lines_target - $header_lines_source) + $source_line;
		$id_target_line = "id_" . $target_line;

		$info[$client][$file_comparable]['target_line'][$target_line] = $key;
		$info[$client][$file_comparable]['source_line'][$target_line] = $key . "=" . $source_text;
		unset ($key);
	}

}

function create_files_pack(&$matrix_info = array(), $client = '', &$client_data = array())
{
	require_once(JPATH_ROOT . '/' . 'modules' . '/' . 'mod_stoolpigeon' . '/' . 'libraries' . '/' . 'pclzip.lib.php');
	$type = $client_data['type'];

	if ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '1')
	{
		$stored_cookies = $matrix_info['stored_cookies'];
		$actual_module  = $matrix_info['actual_module'];
	}

	$new_base_path = $client_data['new_base_path'];
	$files_to_zip  = $client_data['files_to_zip'];

	if ($type == '_edited_files_between_')
	{
		$slt = $matrix_info['config']['source_language_tag'];
		$tlt = $matrix_info['config']['target_language_tag'];
		$sv  = $matrix_info['config']['version_options']['source_version'];
		$tv  = $matrix_info['config']['version_options']['target_version'];

		$zip_name = $client . $type . $slt . "-v" . $sv . "_and_" . $tlt . "-v" . $tv . ".zip";

	}
	elseif ($type == '_converted_to_')
	{
		$slt = $matrix_info['config']['source_language_tag'];
		$tlt = $matrix_info['config']['target_language_tag'];
		$sv  = $matrix_info['config']['version_options']['source_version'];
		$tv  = $matrix_info['config']['version_options']['target_version'];

		$zip_name = $client . "_" . $tlt . "-v" . $tv . $type . $slt . "-v" . $sv . ".zip";
	}
	elseif ($type == 'backup_target_files_')
	{

		$tlt      = $matrix_info['config']['target_language_tag'];
		$tv       = $matrix_info['config']['version_options']['target_version'];
		$zip_name = 'backup_target_' . $client . '_files_' . $tlt . '-v' . $tv . '.zip';
	}
	elseif ($type == 'sort_target_keys_')
	{

		$tlt      = $matrix_info['config']['target_language_tag'];
		$tv       = $matrix_info['config']['version_options']['target_version'];
		$zip_name = 'sorted_target_' . $client . '_files_' . $tlt . '-v' . $tv . '.zip';
	}

	$archivo = new PclZip($zip_name);

	$v_list = $archivo->create($files_to_zip,
		PCLZIP_OPT_REMOVE_PATH, $new_base_path . '/' . $client);

	if ($v_list == 0)
	{

		die("Error : " . $archivo->errorInfo(true));

	}
	else
	{
		echo "<br />" . JText::_('MOD_STOOLPIGEON_THE_PACKAGE') . "<b>" . $zip_name . "</b>"
			. JText::_('MOD_STOOLPIGEON_PACKAGE_AT_ROOT_FOLDER') . "<br />";

		$dpl = $matrix_info['config']['experimental_options']['display_package_link'];
		if ($dpl == '1')
		{
			echo JText::_('MOD_STOOLPIGEON_DOWNLOAD') . "<br />";
			echo "<br /><a href=" . JURI::base() . $zip_name . ">" . $zip_name . "</a><br /><br />";
		}
		$discart = $client_data['discart_changes'];
		if ($discart == '1')
		{
			diff_tags_delete_stored_cookies($stored_cookies, $actual_module);
			JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_STORED_DIFF_TAGS_DELETED'));
		}

		echo JText::_('MOD_STOOLPIGEON_REPORTED_INFO_BELLOW') . "<br />";
	}

	if (!JFolder::delete($new_base_path))
	{
	}


}//end create_files_pack


function determine_client_availability(&$matrix_info = array(), $client = '', &$client_data = array())
{
	$source_language_tag = $matrix_info['config']['source_language_tag'];
	$target_language_tag = $matrix_info['config']['target_language_tag'];
	$client_base_path    = $client_data['client_base_path'];//Admin = JPATH_ADMINISTRATOR, Site and installation = JPATH_ROOT
	$short_text          = $client_data['short_text'];//A = Administrator, S= Site, I= Installation.
	$allow_liar_use      = $matrix_info['config']['experimental_options']['allow_liar_use'];

	$source_client_folder = $client_data['source_client_folder'];
	$target_client_folder = $client_data['target_client_folder'];

	$client_is_selected       = $client_data['client_is_selected'];
	$site_is_selected         = $matrix_info['config']['site_selected'];
	$installation_is_selected = $matrix_info['config']['installation_selected'];

	$source_site_folder         = $matrix_info['config']['source_site_folder'];
	$target_site_folder         = $matrix_info['config']['target_site_folder'];
	$source_installation_folder = $matrix_info['config']['source_installation_folder'];
	$target_installation_folder = $matrix_info['config']['target_installation_folder'];
	$source_l_folder            = $matrix_info['config']['paths']['s' . strtolower($short_text) . 'f'];
	$target_l_folder            = $matrix_info['config']['paths']['t' . strtolower($short_text) . 'f'];
	$source_l_folder_exist      = JFolder::exists(JPath::check($source_l_folder));
	$target_l_folder_exist      = JFolder::exists(JPath::check($target_l_folder));


	if ($client_is_selected == '1' && $source_l_folder_exist == '1' && $target_l_folder_exist == '1')
	{
		if ($client == 'admin' || $client == 'site' || $client == 'installation')
		{

			if (($client == 'site' || $client == 'installation') && ($site_is_selected == '1' && $installation_is_selected == '1'))
			{
				//avoid cross site and installation folders by mistake.
				if (
					($source_installation_folder != $source_site_folder && $target_installation_folder != $target_site_folder)
					&&
					($target_installation_folder != $source_site_folder && $source_installation_folder != $target_site_folder)
				)
				{
					if ($source_language_tag == $target_language_tag)
					{
						//normal use or allow alternatives too
						if ($allow_liar_use == '1' || $source_client_folder != $target_client_folder)
						{
							$matrix_info['mode']                                = 'between_equal_language_tags';
							$matrix_info['config']['client_selection'][$client] = '1';

						}
						elseif ($allow_liar_use == '0' && $source_client_folder == $target_client_folder)
						{
							//wrong configuration detection when the client is selected.
							$mode                                               = 'incompatible';
							$matrix_info['messages']['errors']['general'][]     =
								"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_WRONG_CONFIGURATION_' . $short_text . 'Z')
								. "</font><br /><font color='red'><p>" . JText::_('MOD_STOOLPIGEON_EQUAL_TAGS_AND_NOT_DIFF_FOLDERS')
								. "<p></font><br />";
							$matrix_info['config']['client_selection'][$client] = '2';
						}

					}
					elseif ($source_language_tag != $target_language_tag)
					{
						//normal use or allow alternatives too
						if ($allow_liar_use == '1' || $source_client_folder == $target_client_folder)
						{
							$matrix_info['mode']                                = 'between_different_language_tags';
							$matrix_info['config']['client_selection'][$client] = '1';

						}
						elseif ($allow_liar_use == '0' && $source_client_folder != $target_client_folder)
						{
							//wrong configuration detection when the client is selected.
							$mode                                               = 'incompatible';
							$matrix_info['messages']['errors']['general'][]     = "<br /><font color='#000033'>"
								. JText::_('MOD_STOOLPIGEON_WRONG_CONFIGURATION_'
									. $short_text . 'Z') . "</font><br /><font color='red'><p>"
								. JText::_('MOD_STOOLPIGEON_DIFF_TAGS_AND_NOT_EQUAL_FOLDERS')
								. "<p></font><br />";
							$matrix_info['config']['client_selection'][$client] = '2';
						}
					}


				}
				elseif ($source_installation_folder == $source_site_folder)
				{
					$matrix_info['messages']['errors']['general'][]     = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_WRONG_S_AND_I_CONFLICT') . "</font><br /><font color='black'>"
						. JText::_('MOD_STOOLPIGEON_EQUAL_FOLDERS_FOR_SI_AND_SS') . "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_SIF') . "</font><font color='red'>" . $source_installation_folder
						. "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_SSF') . "</font><font color='red'>" . $source_site_folder . "</font><br />";
					$matrix_info['config']['client_selection'][$client] = '2';
				}
				elseif ($target_installation_folder == $target_site_folder)
				{
					$matrix_info['messages']['errors']['general'][]     = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_WRONG_S_AND_I_CONFLICT') . "</font><br /><font color='black'>"
						. JText::_('MOD_STOOLPIGEON_EQUAL_FOLDERS_FOR_TI_AND_TS') . "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_TIF') . "</font><font color='red'>" . $target_installation_folder
						. "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_TSF') . "</font><font color='red'>" . $target_site_folder . "</font><br />";
					$matrix_info['config']['client_selection'][$client] = '2';

				}
				elseif ($target_installation_folder == $source_site_folder)
				{
					$matrix_info['messages']['errors']['general'][]     = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_WRONG_S_AND_I_CONFLICT') . "</font><br /><font color='black'>"
						. JText::_('MOD_STOOLPIGEON_EQUAL_FOLDERS_FOR_TI_AND_SS') . "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_TIF') . "</font><font color='red'>" . $target_installation_folder
						. "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_SSF') . "</font><font color='red'>" . $source_site_folder . "</font><br />";
					$matrix_info['config']['client_selection'][$client] = '2';

				}
				elseif ($source_installation_folder == $target_site_folder)
				{
					$matrix_info['messages']['errors']['general'][]     = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_WRONG_S_AND_I_CONFLICT') . "</font><br /><font color='black'>"
						. JText::_('MOD_STOOLPIGEON_EQUAL_FOLDERS_FOR_SI_AND_TS') . "</font><br /><font color='darkorange'>"
						. JText::_('MOD_STOOLPIGEON_SIF') . "</font><font color='red'>" . $source_installation_folder
						. "</font><br /><font color='darkorange'>" . JText::_('MOD_STOOLPIGEON_TSF')
						. "</font><font color='red'>" . $target_site_folder . "</font><br />";
					$matrix_info['config']['client_selection'][$client] = '2';
				}

			}
			else
			{
				//site and installation are not crosseds or one of both is not a selected client, follow normal rules.

				if ($source_language_tag == $target_language_tag)
				{
					//normal use or allow alternatives too
					if ($allow_liar_use == '1' || $source_client_folder != $target_client_folder)
					{
						$matrix_info['mode']                                = 'between_equal_language_tags';
						$matrix_info['config']['client_selection'][$client] = '1';

					}
					elseif ($allow_liar_use == '0' && $source_client_folder == $target_client_folder)
					{
						//wrong configuration detection when the client is selected.
						$mode                                               = 'incompatible';
						$matrix_info['messages']['errors']['general'][]     =
							"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_WRONG_CONFIGURATION_' . $short_text . 'Z')
							. "</font><br /><font color='red'><p>" . JText::_('MOD_STOOLPIGEON_EQUAL_TAGS_AND_NOT_DIFF_FOLDERS')
							. "<p></font><br />";
						$matrix_info['config']['client_selection'][$client] = '2';
					}

				}
				elseif ($source_language_tag != $target_language_tag)
				{
					//normal use or allow alternatives too
					if ($allow_liar_use == '1' || $source_client_folder == $target_client_folder)
					{
						$matrix_info['mode']                                = 'between_different_language_tags';
						$matrix_info['config']['client_selection'][$client] = '1';

					}
					elseif ($allow_liar_use == '0' && $source_client_folder != $target_client_folder)
					{
						//wrong configuration detection when the client is selected.
						$mode                                               = 'incompatible';
						$matrix_info['messages']['errors']['general'][]     = "<br /><font color='#000033'>"
							. JText::_('MOD_STOOLPIGEON_WRONG_CONFIGURATION_'
								. $short_text . 'Z') . "</font><br /><font color='red'><p>"
							. JText::_('MOD_STOOLPIGEON_DIFF_TAGS_AND_NOT_EQUAL_FOLDERS')
							. "<p></font><br />";
						$matrix_info['config']['client_selection'][$client] = '2';
					}
				}
			}
		}
		//wrong configuration detection when the client is selected.
	}
	elseif ($client_is_selected == '1' && $source_l_folder_exist == '')
	{
		$matrix_info['messages']['errors']['general'][]     =
			"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_FOLDER_INEXISTENT_S' . $short_text)
			. "</font><br />" . JPATH_ROOT . "/" . "<font color='red'>$source_client_folder</font><br />";
		$matrix_info['config']['client_selection'][$client] = '2';

	}
	elseif ($client_is_selected == '1' && $target_l_folder_exist == '')
	{
		$matrix_info['messages']['errors']['general'][]     =
			"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_FOLDER_INEXISTENT_T' . $short_text)
			. "</font><br />" . JPATH_ROOT . "/" . "<font color='red'>$target_client_folder</font><br />";
		$matrix_info['config']['client_selection'][$client] = '2';

	}
	elseif ($client_is_selected == '')
	{
		$matrix_info['config']['client_selection'][$client] = '0';
	}

} //end determine client availability fucntion

function catch_files_by_type(&$matrix_info = array(), $client = '', &$client_data = array())
{
//first put all types in matrix and then revise only the type inis (equals, quotes, etc), catch comparables, and more info.
	$source_language_tag = $matrix_info['config']['source_language_tag'];
	$target_language_tag = $matrix_info['config']['target_language_tag'];

	if ($client == 'admin')
	{
		$source_path = $matrix_info['config']['paths']['saf'];
		$target_path = $matrix_info['config']['paths']['taf'];

	}
	elseif ($client == 'site')
	{

		$source_path = $matrix_info['config']['paths']['ssf'];
		$target_path = $matrix_info['config']['paths']['tsf'];

	}
	elseif ($client == 'installation')
	{

		$source_path = $matrix_info['config']['paths']['sif'];
		$target_path = $matrix_info['config']['paths']['tif'];
	}

	$locations       = array('source', 'target');
	$mandatory_files = $client_data['mandatory_files'];
	$short_text      = $client_data['short_text'];
	foreach ($locations as $location)
	{

		if ($location == 'source')
		{
			$language_tag        = $matrix_info['config']['source_language_tag'];
			$client_folder       = $matrix_info['config']['paths']['s' . strtolower($short_text) . 'f'];
			$target_folder       = $matrix_info['config']['paths']['t' . strtolower($short_text) . 'f'];
			$target_language_tag = $matrix_info['config']['target_language_tag'];
			$short_location      = 'S';
			$hide_info           = $matrix_info['config']['display_options']['hide_source_info'];
			//Requireds when revise_file_content is running.
			$client_data['location']       = $location;
			$client_data['short_location'] = 'S';
			$client_data['hide_info']      = $matrix_info['config']['display_options']['hide_source_info'];
			$client_data['language_tag']   = $language_tag;

		}
		elseif ($location == 'target')
		{
			$language_tag   = $matrix_info['config']['target_language_tag'];
			$client_folder  = $matrix_info['config']['paths']['t' . strtolower($short_text) . 'f'];
			$short_location = 'T';
			$hide_info      = $matrix_info['config']['display_options']['hide_target_info'];
			//Requireds when revise_file_content is running.
			$client_data['location']       = $location;
			$client_data['short_location'] = 'T';
			$client_data['hide_info']      = $matrix_info['config']['display_options']['hide_target_info'];
			$client_data['language_tag']   = $language_tag;
		}

		foreach ($mandatory_files as $type_format => $files)
		{

			if ($type_format == 'admin_ini_files'
				|| $type_format == 'site_ini_files'
				|| $type_format == 'installation_ini_files')
			{
				$short_type                = 'TI';
				$client_data['short_type'] = $short_type;
			}
			elseif ($type_format == 'admin_non_ini_files'
				|| $type_format == 'site_non_ini_files'
				|| $type_format == 'installation_non_ini_files')
			{
				$short_type                = 'TNI';
				$client_data['short_type'] = $short_type;

			}
			elseif ($type_format == 'admin_non_ini_tagged_files'
				|| $type_format == 'site_non_ini_tagged_files'
				|| $type_format == 'installation_non_ini_tagged_files')
			{
				$short_type                = 'TNIT';
				$client_data['short_type'] = $short_type;
			}
			foreach ($files as $file)
			{

				if (empty($matrix_info['mandatories'][$client][$location]))
				{
					$matrix_info['mandatories'][$client][$location][] = $file;
					if ($location == 'source')
					{
						//the bucle revise source and target and we only need to add mandatories one time.
						$matrix_info['mandatories'][$client][]      = $file;
						$matrix_info['mandatories'][$type_format][] = $file;
					}
					if ($short_type == 'TNI')
					{
						$full_path = $client_folder . '/' . $file;
					}
					else
					{
						$full_path = $client_folder . '/' . $language_tag . '.' . $file;
					}

					if (JFile::exists($full_path))
					{
						$matrix_info['file_existent'][$type_format][$location]['files'][] = $file;
						//required when revise_file_content.
						$client_data['file']           = $file;
						$client_data['full_file_path'] = $full_path;
						$client_data['language_tag']   = $language_tag;


						if ($short_type == 'TI')
						{
							revise_file_content($matrix_info, $client, $client_data);
						}

						//catch comparables

						if ($location == 'source'
							&& $short_type == 'TI'
							&& JFile::exists($target_path . '/' . $target_language_tag . '.' . $file))
						{
							$matrix_info[$client . '_comparables'][] = $file;
						}

					}
					else
					{

						$matrix_info['incidences']['file_non_existent'][$type_format][$location][] = $file;

						if ($hide_info == '0')
						{
							if ($short_type == 'TNI')
							{
								$file_name = $file;
							}
							else
							{
								$file_name = $language_tag . '.' . $file;
							}

							if ($language_tag == 'en-GB' && $file_name == 'install.xml')
							{
								//normal scenario with this file and language tag.
							}
							else
							{
								$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
									. JText::_('MOD_STOOLPIGEON_MANDATORY_FILE_NOT_FOUND_'
										. $short_location . $short_text . '_' . $short_type)
									. "</font><br /><font color='red'>$file_name</font><br />";
							}
						}
					}
				}
				elseif (!in_array($file, $matrix_info['mandatories'][$client][$location]))
				{
					$matrix_info['mandatories'][$client][$location][] = $file;

					if ($location == 'source')
					{
						//bucle revise source and target and we only need to add mandatories one time.
						$matrix_info['mandatories'][$client][]      = $file;
						$matrix_info['mandatories'][$type_format][] = $file;
					}

					if ($short_type == 'TNI')
					{
						$full_path = $client_folder . '/' . $file;
					}
					else
					{
						$full_path = $client_folder . '/' . $language_tag . '.' . $file;
					}

					if (JFile::exists($full_path))
					{
						$matrix_info['file_existent'][$type_format][$location]['files'][] = $file;
						//required when revise_file_content.
						$client_data['file']           = $file;
						$client_data['full_file_path'] = $full_path;


						if ($short_type == 'TI')
						{
							revise_file_content($matrix_info, $client, $client_data);
						}
						//catch comparables
						if ($location == 'source'
							&& $short_type == 'TI'//Type Ini only.
							&& JFile::exists($target_path . '/' . $target_language_tag . '.' . $file))
						{
							$matrix_info[$client . '_comparables'][] = $file;
						}

					}
					else
					{

						$matrix_info['incidences']['file_non_existent'][$type_format][$location][] = $file;

						if ($hide_info == '0')
						{
							if ($short_type == 'TNI')
							{
								$file_name = $file;
							}
							else
							{
								$file_name = $language_tag . '.' . $file;
							}

							if ($language_tag == 'en-GB' && $file_name == 'install.xml')
							{
								//normal scenario with this file and language tag.
							}
							else
							{
								$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
									. JText::_('MOD_STOOLPIGEON_MANDATORY_FILE_NOT_FOUND_'
										. $short_location . $short_text . '_' . $short_type)
									. "</font><br /><font color='red'>$file_name</font><br />";
							}
						}
					}

				}
				elseif (isset($matrix_info['mandatories'][$client][$location]))
				{

					if (in_array($file, $matrix_info['mandatories'][$client][$location]))
					{
						if ($location == 'source')
						{
							//The bucle revise source and target and we only need to add mandatory files that are duplicated one time.
							$matrix_info['messages']['errors']['general'][]                                =
								"<br /><font color='#000033'>[T#011] MANDATORY FILE: </font>"
								. "<font color='red'>" . $file . "</font>"
								. "<font color='#000033'> ALREADY INSERTED AT THE MODULE CONFIGURATION FIELD</font>"
								. "<br /><font color='black'>CONFIG ZONE TO REVISE: " . $client . "</font><br />";
							$matrix_info['duplicated_mandatory_files'][$client][$location][$type_format][] = $file;
						}
					}
				}

				unset ($file);
			}
			unset ($type_format, $files);
		}
		unset ($location);
	}// foreach locations as location.
//we can to unset this one because is not needed store 3 times same info and "count" can to lie.

	unset ($matrix_info['mandatories'][$client]['source']);
	unset ($matrix_info['mandatories'][$client]['target']);

	//one time all the source and target are catched, if comparables... time to catch more stuff there.
	if (!empty($matrix_info[$client . '_comparables']))
	{
		foreach ($matrix_info[$client . '_comparables'] as $file)
		{
			$client_data['file']           = $file;
			$client_data['full_file_path'] = $client_folder . '/' . $language_tag . '.' . $file;
			extract_diff_between_comparable_files($matrix_info, $client, $client_data);
			unset ($file);
		}
	}
} //end catch files by type

function revise_file_content(&$matrix_info = array(), $client = '', &$client_data = array())
{
	$full_file_path = $client_data['full_file_path'];
	$file           = $client_data['file'];
	$language_tag   = $client_data['language_tag'];
	$location       = $client_data['location'];
	$short_location = $client_data['short_location'];
	$short_text     = $client_data['short_text'];
	$short_type     = $client_data['short_type'];
	$hide_info      = $client_data['hide_info'];

	$catch_quotes                       = $matrix_info['config']['version_options']['catch_quotes'];
	$bom_as_system_message              = $matrix_info['config']['system_messages_options']['bom_as_system_message'];
	$eol_as_system_message              = $matrix_info['config']['system_messages_options']['eol_as_system_message'];
	$missed_quotes_as_system_message    = $matrix_info['config']['system_messages_options']['missed_quotes_as_system_message'];
	$extra_space_as_system_message      = $matrix_info['config']['system_messages_options']['extra_space_as_system_message'];
	$bad_usage_quotes_as_system_message = $matrix_info['config']['system_messages_options']['bad_usage_quotes_as_system_message'];
	$missed_equal_as_system_message     = $matrix_info['config']['system_messages_options']['missed_equal_as_system_message'];

	$catched_ini_file = @file_get_contents($full_file_path);
//replaced due seems this one can to fail with Mac or Windows EOL format
//$catched_ini_file_lines = explode("\n", $catched_ini_file);
	$catched_ini_file_lines = preg_split('/\r\n|\r|\n/', $catched_ini_file);


	if (preg_match('/\x0A/s', $catched_ini_file) == 0 || preg_match('/\x0D\x0A/s', $catched_ini_file) != 0)
	{
		if ($hide_info == '0' && $eol_as_system_message == '1')
		{
			$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
				. JText::_('MOD_STOOLPIGEON_PP_ERROR_EOF_' . $short_location . $short_text)
				. "</font><br /><font color='red'>" . $language_tag . "." . $file . "</font><br />";
		}
	}

	if (mb_check_encoding($catched_ini_file) == false)
	{
		if ($hide_info == '0')
		{
			$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
				. JText::_('MOD_STOOLPIGEON_PP_ERROR_FILE_NO_UTF_' . $short_location . $short_text)
				. "</font><br /><font color='red'>" . $language_tag . "." . $file . "</font><br />";
		}
	}

	if ($catched_ini_file [0] == "\xEF" && $catched_ini_file [1] == "\xBB" && $catched_ini_file [2] == "\xBF")
	{
		if ($hide_info == '0' && $bom_as_system_message == '1')
		{
			$eol_detected                                   = '1';
			$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
				. JText::_('MOD_STOOLPIGEON_PP_ERROR_BOM_' . $short_location . $short_text) . "</font><br /><font color='red'>"
				. $language_tag . "." . $file . "</font><br />";
		}
		else
		{
			$eol_detected = '0';
		}

	}
	else
	{
		$eol_detected = '0';
	}

	$line_counter      = '0';
	$keys_amount       = '0';
	$count_line_words  = '0';
	$count_lines_words = '0';
	$first_key         = '0';
	$first_key_catched = '0';

	foreach ($catched_ini_file_lines as $line)
	{

		$trimmed_line     = trim($line);
		$trimmed_line_len = strlen($trimmed_line) - 1;

		if ((empty($line)) || ($line{0} == '#') || ($line{0} == ';'))
		{
			$line_counter++;

			if ($first_key_catched == '0')
			{
				$first_key++;
			}

			continue;

		}
		elseif (strpos($line, '='))
		{
			$first_key_catched = '1';
			list($key, $value) = explode('=', $line, 2);
			$trimmed_key             = trim($key);
			$trimmed_value           = trim($value);
			$trimmed_value_Q_cleaned = preg_replace('/"_QQ_"/', '', $trimmed_value);

			$line_counter++;
			$len                   = strlen($value) - 1;
			$trimmed_len           = strlen($trimmed_value) - 1;
			$trimmed_len_Q_cleaned = strlen($trimmed_value_Q_cleaned) - 1;
			$init_qq               = (mb_substr($trimmed_value, 0, 6, 'UTF-8'));
			$final_qq              = substr($trimmed_value, -6);

			if ($catch_quotes == '1')
			{
				if ($trimmed_value{0} !== '"')
				{
					if ($missed_quotes_as_system_message == '1' && $hide_info == '0')
					{

						$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
							. JText::_('MOD_STOOLPIGEON_PP_ERROR_LQNF_' . $short_location . $short_text)
							. "</font><br /><font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
					<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
							. "Catched text --->" . "</font><font color='red'>$value</font><font color='black'>" . "|---" . "</font><br />";
					}
					if ($hide_info == '0')
					{
						$matrix_info['incidences']['quote_not_found'][$location][$client]['line'][$file][$key] = $line_counter;

						$matrix_info['incidences']['quote_not_found'][$location][$client]['text'][$file][$line_counter] = $value;
					}
				}

				if ($trimmed_value{$trimmed_len} !== '"')
				{
					if ($missed_quotes_as_system_message == '1' && $hide_info == '0')
					{
						$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
							. JText::_('MOD_STOOLPIGEON_PP_ERROR_RQNF_' . $short_location . $short_text)
							. "</font><br /><font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
					<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
							. "Catched text ---|" . "</font><font color='red'>$value</font><font color='black'>" . "<---" . "</font><br />";
					}

					if ($hide_info == '0')
					{
						$matrix_info['incidences']['quote_not_found'][$location][$client]['line'][$file][$key] = $line_counter;

						$matrix_info['incidences']['quote_not_found'][$location][$client]['text'][$file][$line_counter] = $value;
					}
				}

				if ($extra_space_as_system_message == '1' && $value{0} !== '"' && $trimmed_value{0} == '"')
				{

					if ($hide_info == '0')
					{
						$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
							. JText::_('MOD_STOOLPIGEON_FS_WARNING_ES_FQ_' . $short_location . $short_text)
							. "</font><br /><font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
					<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
							. "Catched text --->" . "</font><font color='red'>$value</font><font color='black'>" . "|---" . "</font><br />";
					}

					if ($hide_info == '0')
					{
						$matrix_info['incidences']['extra_space'][$location][$client]['line'][$file][$key] = $line_counter;

						$matrix_info['incidences']['extra_space'][$location][$client]['text'][$file][$line_counter] = $value;
					}
				}

				if ($extra_space_as_system_message == '1' && $value{$len} !== '"' && $trimmed_value{$trimmed_len} == '"')
				{

					if ($hide_info == '0')
					{
						$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
							. JText::_('MOD_STOOLPIGEON_FS_WARNING_ES_LQ_' . $short_location . $short_text)
							. "</font><br /><font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
				<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
							. "Catched text ---|" . "</font><font color='red'>$value</font><font color='black'>" . "<---" . "</font><br />";
					}

					if ($hide_info == '0')
					{
						$matrix_info['incidences']['extra_space'][$location][$client]['line'][$file][$key] = $line_counter;

						$matrix_info['incidences']['extra_space'][$location][$client]['text'][$file][$line_counter] = $value;
					}
				}

				if ($trimmed_value{0} == '"' && $trimmed_value{$trimmed_len} == '"')
				{
					if ($init_qq == '"_QQ_"')
					{
						$unquoted_l_trimmed_value = substr($trimmed_value, 6);
					}
					else
					{
						$unquoted_l_trimmed_value = substr($trimmed_value, 1);
					}

					if ($final_qq == '"_QQ_"')
					{
						$unquoted_trimmed_value = substr($unquoted_l_trimmed_value, 0, -6);
					}
					else
					{
						$unquoted_trimmed_value = substr($unquoted_l_trimmed_value, 0, -1);
					}


					$cleaned = preg_replace('/"_QQ_"/', 'MOD_CATCHED_Q_', $unquoted_trimmed_value);
					if (strpos($cleaned, '"'))
					{
						$reformated = preg_replace('/"/', 'MOD_CATCHED_Q_', $cleaned);
						$corrected  = preg_replace('/MOD_CATCHED_Q_/', '"_Q_"', $reformated);
						$requoted   = '"' . $corrected . '"';

						if ($bad_usage_quotes_as_system_message == '1' && $hide_info == '0')
						{
							$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
								. JText::_('MOD_STOOLPIGEON_PP_ERROR_SYMBOL_Q_REQUIRED_' . $short_location . $short_text)
								. "</font><br /><font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
					<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
								. "Catched text: </font><font color='red'>$trimmed_value</font><br />";
						}

						if ($hide_info == '0')
						{
							$matrix_info['incidences']['q_required'][$location][$client]['line'][$file][$key] = $line_counter;

							$matrix_info['incidences']['q_required'][$location][$client]['text'][$file][$line_counter] = $value;

							$matrix_info['incidences']['q_required'][$location][$client]['requoted'][$file][$line_counter] = $requoted;
						}

					}

				}

			}

			$key = strtoupper($key);

			$keys_amount++;
			$count_line_words  = count(explode(" ", $value));
			$count_lines_words = $count_lines_words + $count_line_words;

			if (empty($matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
			[$file]['keys'][$key]))
			{

				$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
				[$file]['keys'][$key] = $value;

				$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
				[$file]['counter']['lines'][$key] = $line_counter;


			}
			elseif (!array_key_exists($key, $matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
			[$file]['keys']))
			{

				$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
				[$file]['keys'][$key] = $value;

				$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
				[$file]['counter']['lines'][$key] = $line_counter;

			}
			elseif (array_key_exists($key, $matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info']
			[$file]['keys']))
			{

				//Catching duplicated keys
				$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
					. JText::_('MOD_STOOLPIGEON_PP_DUPLICATED_KEY_' . $short_location . $short_text)
					. "</font><br /><font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
			<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
					. "Catched key: </font><font color='red'>$key</font><br /><font color='black'>"
					. JText::_('MOD_STOOLPIGEON_SYNCHRONISE_DISABLED') . "</font><br />";

				$matrix_info['duplicated_keys'][$location . '_' . $client] = '1';

			}
			else
			{
				echo "something wrong";//for debug control only
			}


		}
		elseif (strpos($line, '=') === false && ($trimmed_line{0} != '[' && $trimmed_line{$trimmed_line_len} != ']'))
		{
			$line_counter++;
			if ($eol_detected == '0' && $missed_equal_as_system_message == '1' && $hide_info == '0')
			{
				if ($bom_as_system_message == '1' && $line_counter == '1')
				{
					$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_PP_ERROR_EQUAL_NOT_FOUNT_' . $short_location . $short_text) . "</font><br />
				<font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
				<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
						. "Catched line: </font><font color='red'>$line</font><br />";
				}
				elseif ($line_counter > '1')
				{
					$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_PP_ERROR_EQUAL_NOT_FOUNT_' . $short_location . $short_text) . "</font><br />
				<font color='black'>File: </font><font color='red'>" . $language_tag . "." . $file . "</font><br />
				<font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
						. "Catched line: </font><font color='red'>$line</font><br />";
				}
			}

			if ($eol_detected == '0' && $hide_info == '0' && $line_counter > '1')
			{
				$matrix_info['incidences']['equal_not_found'][$location][$client]['line'][$file][]              = $line_counter;
				$matrix_info['incidences']['equal_not_found'][$location][$client]['text'][$file][$line_counter] = $line;
			}

		}
		elseif ($trimmed_line{0} == '[' && $trimmed_line{$trimmed_line_len} == ']')
		{
			$line_counter++;
			//if ($eol_detected == '0' && $missed_equal_as_system_message == '1' && $hide_info == '0')
			//{
			//$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
			//. JText::_( 'MOD_STOOLPIGEON_WARNING_NEW_STRING' )
			//. "</font><br /><font color='black'>File: </font><font color='red'>[" . strtoupper($client) . "] " . $language_tag . "." . $file
			//. "</font><br /><font color='black'>Line: </font><font color='red'>$line_counter</font><br /><font color='black'>"
			//. "Catched text: " . "</font><font color='red'>$line</font><br />";
			//}

			if ($eol_detected == '0' && $hide_info == '0')
			{
				$matrix_info['incidences']['with_section_present'][$location][$client]['line_section'][$file][$line] = $line_counter;
				$matrix_info['incidences']['with_section_present'][$location][$client]['line'][$file][]              = $line_counter;
				$matrix_info['incidences']['with_section_present'][$location][$client]['text'][$file][$line_counter] = $line;
			}
		}

		$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info'][$file]['counter']['keys_amount'] =
			$keys_amount;

		$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info'][$file]['counter']['words'] =
			$count_lines_words;

		$matrix_info['file_existent'][$client . '_ini_files'][$location]['files_info'][$file]['counter']['first_key'] =
			$first_key;
		unset($line);
	}
} //end revise_file_content


function extract_diff_between_comparable_files(&$matrix_info = array(), $client = '', &$client_data = array())
{
	$file       = $client_data['file'];
	$short_text = $client_data['short_text'];

	$mode                                 = $matrix_info['mode'];
	$same_words_keys                      = $matrix_info['same_words_keys'];
	$keys_to_keep_in_target               = $matrix_info['keys_to_keep_in_target'];
	$the_keys_to_keep_in_target           = preg_replace('/,\|,\s+/', ',|,', trim($keys_to_keep_in_target));
	$the_keys_to_keep_in_target_explodeds = explode(',|,', $the_keys_to_keep_in_target);
	$source_keys                          = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info'][$file]['keys'];
	$target_keys                          = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info'][$file]['keys'];
	$source_language_tag                  = $matrix_info['config']['source_language_tag'];
	$target_language_tag                  = $matrix_info['config']['target_language_tag'];
//$scape_html = $matrix_info['config']['experimental_options']['scape_html'];
	$missed_keys_as_system_message       = $matrix_info['config']['system_messages_options']['missed_keys_as_system_message'];
	$changed_keys_text_as_system_message = $matrix_info['config']['system_messages_options']['changed_keys_text_as_system_message'];

	if (!isset($matrix_info['means_the_same']))
	{
		$are_same = '';
	}
	else
	{
		$are_same = $matrix_info['means_the_same'];
	}

	if (!isset($matrix_info['means_the_same_errors']))
	{
		$are_same_errors = '';
	}
	else
	{
		$are_same_errors = $matrix_info['means_the_same_errors'];
	}

	if (!isset($matrix_info['avoid_duplicated_are_same']))
	{
		$avoid_duplicated_are_same = array();
	}
	else
	{
		$avoid_duplicated_are_same = $matrix_info['avoid_duplicated_are_same'];
	}

	if (!isset($matrix_info['avoid_duplicated_are_same_errors']))
	{
		$avoid_duplicated_are_same_errors = array();
	}
	else
	{
		$avoid_duplicated_are_same_errors = $matrix_info['avoid_duplicated_are_same_errors'];
	}


	//LANGUAGE SECTIONS BEGIN

	if (isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file])
		&& isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file]))
	{

		$sections_to_delete = array_diff($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file],
			$matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file]);

		$sections_to_add = array_diff($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file],
			$matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file]);

		if (empty($sections_to_add) && empty($sections_to_delete))
		{


		}
		else
		{

			if (!empty($sections_to_delete))
			{
				$section_line = '';
				foreach ($sections_to_delete as $section_to_delete)
				{
					$section_line                                   = $matrix_info['incidences']['with_section_present']['target'][$client]
					['line_section'][$file][$section_to_delete];
					$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_WARNING_SECTION_TO_DELETE')
						. "</font><br /><font color='black'>To delete inside the file: </font><font color='darkorange'>["
						. strtoupper($client) . "] " . $target_language_tag . "." . $file
						. "</font><br /><font color='black'>"
						. "Target line: " . "</font><font color='red'>$section_line</font>"
						. "<br /><font color='black'>"
						. "Catched section: " . "</font><font color='red'>$section_to_delete</font><br />";
				}
			}

			if (!empty($sections_to_add))
			{
				$section_line = '';
				foreach ($sections_to_add as $section_to_add)
				{
					$section_line = $matrix_info['incidences']['with_section_present']['source'][$client]
					['line_section'][$file][$section_to_add];

					$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_WARNING_SECTION_TO_ADD')
						. "</font><br /><font color='black'>To add inside the file: </font><font color='darkorange'>["
						. strtoupper($client) . "] " . $target_language_tag . "." . $file
						. "</font><br /><font color='black'>"
						. "Source line: " . "</font><font color='green'>$section_line</font>"
						. "<br /><font color='black'>"
						. "Catched section: " . "</font><font color='green'>$section_to_add</font><br />";
				}
			}

		}


	}
	elseif (!isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file])
		&& !isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file]))
	{
		//Nothing to do

	}
	elseif (!isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file])
		|| !isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file]))
	{


		if (!empty($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file]))
		{
			$section_line = '';
			foreach ($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file]
			         as $section_to_delete)
			{
				$section_line                                   = $matrix_info['incidences']['with_section_present']['target'][$client]
				['line_section'][$file][$section_to_delete];
				$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
					. JText::_('MOD_STOOLPIGEON_WARNING_SECTION_TO_DELETE')
					. "</font><br /><font color='black'>To delete inside the file: </font><font color='darkorange'>["
					. strtoupper($client) . "] " . $target_language_tag . "." . $file
					. "</font><br /><font color='black'>"
					. "Target line: " . "</font><font color='red'>$section_line</font>"
					. "<br /><font color='black'>"
					. "Catched section: " . "</font><font color='red'>$section_to_delete</font><br />";
			}
		}

		if (!empty($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file]))
		{
			$section_line = '';
			foreach ($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file] as $section_to_add)
			{
				$matrix_info['messages']['errors']['general'][] = "<br /><font color='#000033'>"
					. JText::_('MOD_STOOLPIGEON_WARNING_SECTION_TO_ADD')
					. "</font><br /><font color='black'>To add inside the file: </font><font color='darkorange'>["
					. strtoupper($client) . "] " . $target_language_tag . "." . $file
					. "</font><br /><font color='black'>"
					. "Source line: " . "</font><font color='green'>$section_line</font>"
					. "<br /><font color='black'>"
					. "Catched section: " . "</font><font color='green'>$section_to_add</font><br />";
			}
		}

	}//LANGUAGE SECTIONS END


	foreach ($source_keys as $source_key => $key_text)
	{

		if (!array_key_exists($source_key, $target_keys))
		{
			// key does not exist in target

			if ($missed_keys_as_system_message == '1')
			{
				//($scape_html == '1') ? $key_text = htmlspecialchars ($key_text) : $key_text;
				$key_text   = htmlspecialchars($key_text);
				$source_key = htmlspecialchars($source_key);

				$matrix_info['messages']['notes']['general'][] = "<br /><font color='#000033'>"
					. JText::_('MOD_STOOLPIGEON_KEY_INEXISTENT_SM_T' . $short_text) . $target_language_tag . "." . $file
					. "</font><br /><font color='red'>$source_key=$key_text</font><br />";

			}

			$matrix_info['messages']['notes']['mandatory_source_ini_files'][$client][] = "<br /><font color='#000033'>"
				. JText::_('MOD_STOOLPIGEON_KEY_INEXISTENT_SM_T' . $short_text) . $target_language_tag . "." . $file
				. "</font><br /><font color='red'>$source_key=$key_text</font><br />";

			$matrix_info['incidences']['key_non_existent_in_target'][$client][$file][] = $source_key;

		}
		else
		{
			//key exist in target
			//Works in Joomla! 1.6 and 1.5 (1.5 can to have spaces between keys and a hard separator solve
			$the_keys           = preg_replace('/,\|,\s+/', ',|,', trim($same_words_keys));
			$the_keys_explodeds = explode(',|,', $the_keys);

			if (in_array($source_key, $the_keys_explodeds))
			{

				$confirmed_false_positive = '1';

			}
			else
			{

				$confirmed_false_positive = '0';
			}

			if ($key_text == $target_keys[$source_key])
			{

				//is the same text
				$confirmed_text_is_same = '1';

			}
			else
			{

				//not the same text
				$confirmed_text_is_same = '0';

			}


			if ($mode == 'between_equal_language_tags' && $confirmed_text_is_same == '0')
			{
				// we are comparing same language tags. In example: Joomla! 1.6.3 en-GB against Joomla! 1.6.4 en-GB and the key text is not the same
				$matrix_info['file_existent'][$client . '_ini_files']['source']['files_info'][$file]['keys']
				['between_equal_language_tags']['keys_text_changed'][$source_key] = $key_text;

				if ($matrix_info['config']['experimental_options']['coordinated_task'] == '1')
				{
					setcookie('coordinated_task[' . $source_language_tag . ']'
						. '[' . $matrix_info['config']['version_options']['source_version'] . ']'
						. '[' . $client . '][' . $file . '][keys]'
						. '[' . $source_key . ']', $key_text);
					setcookie('coordinated_task[' . $source_language_tag . ']'
						. '[' . $matrix_info['config']['version_options']['source_version'] . ']'
						. '[' . $client . '][' . $file . '][info]'
						. '[' . $source_key . ']'
						, $matrix_info['config']['version_options']['target_version']);

					//if ($matrix_info['config']['display_options']['display_diff'] == '1')
					//{
					setcookie('coordinated_task[' . $source_language_tag . ']'
						. '[' . $matrix_info['config']['version_options']['source_version'] . ']'
						. '[' . $client . '][' . $file . '][diff]'
						. '[' . $source_key . ']'
						, htmlDiff(htmlspecialchars($target_keys[$source_key]), htmlspecialchars($key_text)));
					//}
					if ($matrix_info['config']['experimental_options']['report_files_keys_addeds_or_deleteds'] == '1')
					{
						echo "<br /><b>" . JText::_('MOD_STOOLPIGEON_COORDINATED_ENABLED') . "</b> | " . strtoupper($client)
							. JText::_('MOD_STOOLPIGEON_COORDINATED_FILE') . $source_language_tag . "." . $file
							. "<br /><font color='green'>" . JText::_('MOD_STOOLPIGEON_COORDINATED_STORING') . "</font>" . $source_key . "<br />";
					}
				}

				if ($changed_keys_text_as_system_message == '1' && $confirmed_text_is_same == '0')
				{
					//($scape_html == '1') ? $key_text_2 = htmlspecialchars ($target_keys[$source_key]) : $key_text_2 = $target_keys[$source_key];
					//($scape_html == '1') ? $key_text = htmlspecialchars ($key_text) : $key_text;
					$key_text_2 = htmlspecialchars($target_keys[$source_key]);
					$key_text   = htmlspecialchars($key_text);
					$source_key = htmlspecialchars($source_key);

					$matrix_info['messages']['notes']['general'][] =
						"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_KEY_TEXT_CHANGED_SM_S' . $short_text) . $source_language_tag
						. "." . $file . "</font><br /><font color='darkgreen'>$source_key</font><br /><table><tr><td class='diff' colspan='5'>"
						. htmlDiff($key_text_2, $key_text) . "</td></tr></table><br />";


				}
				if ($confirmed_text_is_same == '0')
				{
					$matrix_info['messages']['notes']['mandatory_source_ini_files'][$client]['are_not_same'][$target_language_tag . '.' . $file][] =
						$key_text . ",|," . $source_key . ",|," . $target_keys[$source_key];

					$matrix_info['incidences']['between_equal_language_tags'][$client]['keys_text_changed'][$file][] = $source_key;

				}
			}
			elseif ($mode == 'between_different_language_tags' && $confirmed_text_is_same == '1')
			{

				// we are not comparing same language tags. In example: Joomla! 1.6.3 en-GB against Joomla! 1.6.3 es-ES.
				$matrix_info['file_existent'][$client . '_ini_files']['target']['files_info'][$file]
				['between_different_language_tags']['keys_text_unchanged'][$target_keys[$source_key]] = $target_keys[$source_key];

				if ($changed_keys_text_as_system_message == '1' && $confirmed_text_is_same == '1')
				{

					//($scape_html == '1') ? $key_text_2 = htmlspecialchars ($target_keys[$source_key]) : $key_text_2 = $target_keys[$source_key];
					$key_text_2 = htmlspecialchars($target_keys[$source_key]);

					$source_key = htmlspecialchars($source_key);

					$matrix_info['messages']['notes']['general'][] =
						"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_KEY_TEXT_UNTRANSLATED_SM_T' . $short_text)
						. $target_language_tag . "." . $file . "</font><br /><font color='red'>" . $source_key . "=" . $key_text_2 . "</font><br />";
				}

				if ($confirmed_text_is_same == '1' && $confirmed_false_positive == '0')
				{
					$matrix_info['messages']['notes']['mandatory_target_ini_files'][$client]['are_same'][$target_language_tag . '.' . $file][] =
						$key_text . ",|," . $source_key . ",|," . $target_keys[$source_key] . ",|, To translate";

					$matrix_info['incidences']['between_different_language_tags'][$client]['non_translated'][$file][] = $source_key;

					if (!isset($matrix_info['avoid_duplicated_are_same']))
					{
						$matrix_info['avoid_duplicated_are_same'][] = $source_key;
						$are_same                                   .= ",|, " . htmlspecialchars($source_key);
					}
					elseif (!in_array($source_key, $matrix_info['avoid_duplicated_are_same']))
					{
						$matrix_info['avoid_duplicated_are_same'][] = $source_key;
						$are_same                                   .= ",|, " . htmlspecialchars($source_key);
					}
				}

			}
			elseif ($mode == 'between_different_language_tags' && $confirmed_text_is_same == '0' && $confirmed_false_positive == '1')
			{

				//($scape_html == '1') ? $key_text = htmlspecialchars ($key_text) : $key_text;
				//($scape_html == '1') ? $key_text_2 = htmlspecialchars ($target_keys[$source_key]) : $key_text_2 = $target_keys[$source_key];
				$key_text_2 = htmlspecialchars($target_keys[$source_key]);
				$key_text   = htmlspecialchars($key_text);
				$source_key = htmlspecialchars($source_key);

				$matrix_info['messages']['notes']['general'][] = "<br /><font color='#000033'>"
					. JText::_('MOD_STOOLPIGEON_FALSE_POSITIVE_ERROR_SM_T' . $short_text) . $target_language_tag . "." . $file
					. "</font><br /><font color='darkorange'>$source_key</font><font color='red'>"
					. JText::_('MOD_STOOLPIGEON_S_AND_T_IS_NOT_SAME')
					. "</font><br /><font color='darkgreen'>$key_text</font><br /><font color='red'>$key_text_2</font><br />"
					. JText::_('MOD_STOOLPIGEON_YOU_CAN_TO_DELETE_KEY') . "<br />";

				$matrix_info['messages']['notes']['mandatory_target_ini_files'][$client]['false_positives_error'][] =
					"<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_FALSE_POSITIVE_ERROR_SM_T' . $short_text)
					. $target_language_tag . "." . $file
					. "</font><br /><font color='darkorange'>$source_key</font><font color='red'>"
					. JText::_('MOD_STOOLPIGEON_S_AND_T_IS_NOT_SAME')
					. "</font><br />";

				if (!isset($matrix_info['avoid_duplicated_are_same_errors']))
				{
					$matrix_info['avoid_duplicated_are_same_errors'][] = $source_key;
					$are_same_errors                                   .= ",|, " . htmlspecialchars($source_key);
				}
				elseif (!in_array($source_key, $matrix_info['avoid_duplicated_are_same_errors']))
				{
					$matrix_info['avoid_duplicated_are_same_errors'][] = $source_key;
					$are_same_errors                                   .= ",|, " . htmlspecialchars($source_key);
				}

			}

		}

		unset($source_key, $key_text);
	}

	foreach ($target_keys as $target_key => $key_text)
	{

		if (!array_key_exists($target_key, $source_keys))
		{

			if (!in_array($target_key, $the_keys_to_keep_in_target_explodeds))
			{

				// key does not exist in source
				if ($missed_keys_as_system_message == '1')
				{

					//($scape_html == '1') ? $key_text = htmlspecialchars ($key_text) : $key_text;

					$key_text = htmlspecialchars($key_text);

					$target_key = htmlspecialchars($target_key);

					$matrix_info['messages']['notes']['general'][] = "<br /><font color='#000033'>" . JText::_('MOD_STOOLPIGEON_KEY_INEXISTENT_SM_S'
							. $short_text) . $source_language_tag . "." . $file . "</font><br /><font color='red'>$target_key=$key_text</font><br />";
				}

				$matrix_info['messages']['notes']['mandatory_target_ini_files'][$client][] = "<br /><font color='#000033'>"
					. JText::_('MOD_STOOLPIGEON_KEY_INEXISTENT_SM_S' . $short_text) . $source_language_tag . "." . $file
					. "</font><br /><font color='red'>$target_key=$key_text</font><br />";

				$matrix_info['incidences']['key_non_existent_in_source'][$client][$file][] = $target_key;
			}
			else
			{

				if (!isset($matrix_info['incidences']['keys_to_keep_in_target'][$client][$file][$target_key]))
				{
					$matrix_info['incidences']['keys_to_keep_in_target'][$client][$file][$target_key] = $key_text;
					$matrix_info['messages']['notes']['general'][]                                    = "<br /><font color='#000033'>"
						. JText::_('MOD_STOOLPIGEON_NOTICED_KEYS_TO_KEEP') . "</font>"
						. "<br /><b>Client: </b>" . strtoupper($client)
						. "<br /><b>File: </b>" . $source_language_tag . "." . $file
						. "<br /><b>Key: </b>" . $target_key
						. "<br />";
				}
			}


		}
		else
		{
			// key exist in source
		}
		unset($target_key, $key_text);
	}

	$matrix_info['means_the_same']        = $are_same;
	$matrix_info['means_the_same_errors'] = $are_same_errors;

}//end function extract diff between files comparables.

function diff_tags_get_cookies($stored_cookies = '', $client = '', $actual_module = '')
{
	if (isset($stored_cookies['diff_tags'][$actual_module][$client]))
	{
		foreach ($stored_cookies['diff_tags'][$actual_module][$client] as $file => $data)
		{

			foreach ($data as $key => $value)
			{
				//echo "name " . $file;
				$key_s   = $key;
				$value_s = $value;
				//echo "<br />Key: " . $key_s . "<br />Texto: " . $value_s . "<br />\n";
				unset ($key, $value);
			}
			unset ($file, $data);
		}

	}
	else
	{
		//echo "<br />Stored cookies not found at the client: <b>$client</b>";
	}
}

function diff_tags_get_cookie_key($stored_cookies = '', $client = '', $actual_module = '', $cookie_file = '', $cookie_key = '')
{
	if (isset($stored_cookies['diff_tags'][$actual_module][$client]))
	{
		foreach ($stored_cookies['diff_tags'][$actual_module][$client] as $file => $data)
		{

			foreach ($data as $key => $value)
			{
				if ($file == $cookie_file && $key == $cookie_key)
				{
					return htmlspecialchars($value);
				}
				unset ($key, $value);
			}
			unset ($file, $data);
		}

	}
	else
	{
		//echo "<br />Stored cookies not found at the client: <b>$client</b>";
		$value = '';

		return $value;
	}
}


function diff_tags_delete_stored_cookies($stored_cookies = '', $actual_module = '')
{
	if (isset($stored_cookies['diff_tags'][$actual_module]))
	{
		foreach ($stored_cookies['diff_tags'][$actual_module] as $client => $client_data)
		{
			foreach ($stored_cookies['diff_tags'][$actual_module][$client] as $file => $data)
			{

				foreach ($data as $key => $value)
				{
					$cookie_name = "diff_tags[$actual_module][$client][$file][$key]";
					//echo $cookie_name;
					setcookie($cookie_name, "", time() - 3600);
					unset ($key, $value);
				}

				unset ($file, $data);
			}
			unset ($client, $client_data);
		}

		echo "<script type='text/javascript' language='javascript'>window.location.reload();</script>";
		JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_STORED_DIFF_TAGS_DELETED'));

	}
	else
	{
		echo "<br />" . JText::_('MOD_STOOLPIGEON_COOKIES_NOT_FOUND');
	}
}

function coordinated_task_delete_stored_cookies($stored_cookies = '', $source_language_tag = '',
                                                $source_version = '', $target_language_tag = '', $actual_module = '')
{
	if (isset($stored_cookies['coordinated_task'][$source_language_tag][$source_version]))
	{
		foreach ($stored_cookies['coordinated_task'][$source_language_tag][$source_version] as $client => $client_data)
		{
			foreach ($stored_cookies['coordinated_task'][$source_language_tag][$source_version][$client] as $file => $data)
			{

				foreach ($data['keys'] as $key => $value)
				{
					$cookie_name = "coordinated_task[$source_language_tag][$source_version][$client][$file][keys][$key]";
					//echo $cookie_name;
					setcookie($cookie_name, "", time() - 3600);

					//One time we are deleting the coordinated task info, is required
					//to delete the possible stored "diff_tags" changes comming from there too
					$target_file = $target_language_tag . '.' . $file;

					if (isset($stored_cookies['diff_tags'][$actual_module][$client][$target_file][$key]))
					{
						$cookie_name = "diff_tags[$actual_module][$client][$target_file][$key]";
						//echo $cookie_name;
						setcookie($cookie_name, "", time() - 3600);
					}
					unset ($key, $value);
				}

				foreach ($data['info'] as $key => $value)
				{
					$cookie_name = "coordinated_task[$source_language_tag][$source_version][$client][$file][info][$key]";
					//echo $cookie_name;
					setcookie($cookie_name, "", time() - 3600);
					unset ($key, $value);
				}

				if (isset($data['diff']))
				{
					foreach ($data['diff'] as $key => $value)
					{
						$cookie_name = "coordinated_task[$source_language_tag][$source_version][$client][$file][diff][$key]";
						//echo $cookie_name;
						setcookie($cookie_name, "", time() - 3600);
						unset ($key, $value);
					}
				}
				unset ($file, $data);
			}
			unset ($client, $client_data);
		}
		echo "<script type='text/javascript' language='javascript'>window.location.reload();</script>";
		JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_STORED_COORDINATED_TASK_DELETED'));

	}
	else
	{
		echo "<br />" . JText::_('MOD_STOOLPIGEON_COOKIES_NOT_FOUND');
	}
}

function coordinated_task_delete_stored_cookies_by_client($stored_cookies = '', $client = '',
                                                          $source_language_tag = '', $source_version = '', $target_language_tag = '', $actual_module = '')
{
	if (isset($stored_cookies['coordinated_task'][$source_language_tag][$source_version][$client]))
	{
		foreach ($stored_cookies['coordinated_task'][$source_language_tag][$source_version][$client] as $file => $data)
		{

			foreach ($data['keys'] as $key => $value)
			{
				$cookie_name = "coordinated_task[$source_language_tag][$source_version][$client][$file][keys][$key]";
				//echo $cookie_name;
				setcookie($cookie_name, "", time() - 3600);

				//One time we are deleting the coordinated task info, is required
				//to delete the possible stored "diff_tags" changes comming from there too
				$target_file = $target_language_tag . '.' . $file;

				if (isset($stored_cookies['diff_tags'][$actual_module][$client][$target_file][$key]))
				{
					$cookie_name = "diff_tags[$actual_module][$client][$target_file][$key]";
					//echo $cookie_name;
					setcookie($cookie_name, "", time() - 3600);
				}
				unset ($key, $value);
			}

			foreach ($data['info'] as $key => $value)
			{
				$cookie_name = "coordinated_task[$source_language_tag][$source_version][$client][$file][info][$key]";
				//echo $cookie_name;
				setcookie($cookie_name, "", time() - 3600);
				unset ($key, $value);
			}

			if (isset($data['diff']))
			{
				foreach ($data['diff'] as $key => $value)
				{
					$cookie_name = "coordinated_task[$source_language_tag][$source_version][$client][$file][diff][$key]";
					//echo $cookie_name;
					setcookie($cookie_name, "", time() - 3600);
					unset ($key, $value);
				}
			}
			unset ($file, $data);
		}

		echo "<script type='text/javascript' language='javascript'>window.location.reload();</script>";
		JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_STORED_COORDINATED_TASK_DELETED'));

	}
	else
	{
		//echo "<br />Stored cookies not found at the client: <b>$client</b>";
	}
}

function diff_tags_set_changes(&$matrix_info = array(), $client = '', &$client_data = array())
{

	$actual_module        = $matrix_info['actual_module'];
	$display_package_link = $matrix_info['config']['experimental_options']['display_package_link'];
	$stored_cookies       = $matrix_info['stored_cookies'];
	$new_base_path        = $client_data['new_base_path'];
	$files_to_zip         = $client_data['files_to_zip'];

	if ($client == 'admin')
	{
		$target_path = $matrix_info['config']['paths']['taf'];
	}
	elseif ($client == 'site')
	{
		$target_path = $matrix_info['config']['paths']['tsf'];
	}
	elseif ($client == 'installation')
	{
		$target_path = $matrix_info['config']['paths']['tif'];
	}


	if (isset($stored_cookies['diff_tags'][$actual_module][$client]))
	{

		if (!JFolder::create($new_base_path))
		{
		}
		if (!JFolder::create($new_base_path . '/' . $client))
		{
		}

		$client_editeds = array();
		if (!array_key_exists($client, $client_editeds))
		{
			$client_editeds[$client] = 'CATCHED';
			$file_editeds            = array();

			foreach ($stored_cookies['diff_tags'][$actual_module][$client] as $file => $data)
			{
				//echo "<br />File" . $file;
				$keys_editeds     = array();
				$keys_inserteds   = array();
				$new_file_content = '';

				$target_file_path = $target_path . '/' . $file;

				$new_target_file_path = $new_base_path . '/' . $client . '/' . $file;

				$catched_file_content = JFile::read($target_file_path);
				//replaced due seems this one can to fail with Mac or Windows EOL format
				//$catched_file_content_lines = explode("\n", $catched_file_content);
				$catched_file_content_lines = preg_split('/\r\n|\r|\n/', $catched_file_content);


				$file_editeds[$file] = 'OPENED';

				foreach ($catched_file_content_lines as $line)
				{

					$trimmed_line = trim($line);

					if ((empty($line)) || ($line{0} == '#') || ($line{0} == ';') || ($trimmed_line{0} == '['))
					{
						$new_file_content .= $line . "\n";
					}
					else
					{

						list($target_key, $old_value) = explode('=', $line, 2);

						if (array_key_exists($target_key, $data) && !array_key_exists($target_key, $keys_editeds))
						{
							$new_file_content          .= trim($target_key . "=" . $data[$target_key]) . "\n";
							$keys_editeds[$target_key] = 'CHANGED';
							if ($matrix_info['config']['experimental_options']['report_files_keys_addeds_or_deleteds'] == '1')
							{
								echo "<br /><font color='green'>" . JText::_('MOD_STOOLPIGEON_CHANGED')
									. "</font>" . JText::_('MOD_STOOLPIGEON_' . strtoupper($client) . '_MISSED_KEY')
									. $file
									. JText::_('MOD_STOOLPIGEON_SANITIZED_KEY') . $target_key . "<br />";
							}

						}
						elseif (!array_key_exists($target_key, $data) && !array_key_exists($target_key, $keys_inserteds))
						{
							$new_file_content            .= $line . "\n";
							$keys_inserteds[$target_key] = 'INSERTED';
						}
					}
					unset ($line);
				}
				$trimmednew_file_content = trim($new_file_content);
				JFile::write($new_target_file_path, $trimmednew_file_content);
				$files_to_zip .= $new_target_file_path . ",";
				unset ($file, $data);
			}

			if ($files_to_zip != '')
			{
				$client_data['files_to_zip'] = $files_to_zip;
				create_files_pack($matrix_info, $client, $client_data);
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_WITHOUT_FILES_TO_SEND') . $client);
			}
		}


	}
	else
	{
		echo "<br />" . JText::_('MOD_STOOLPIGEON_COOKIES_NOT_FOUND') . ": <b>" . $client . "</b>";
	}
}//end diff_tags_set_changes


function display_means_same_table(&$matrix_info = array(), &$means_same_table = array())
{
	$clients_to_show = '0';
	if ((isset($matrix_info['config']['client_selection']['admin']) && $matrix_info['config']['client_selection']['admin'] == '1')
		|| (isset($matrix_info['config']['client_selection']['site']) && $matrix_info['config']['client_selection']['site'] == '1')
		|| (isset($matrix_info['config']['client_selection']['installation']) && $matrix_info['config']['client_selection']['installation'] == '1'))
	{
		$clients_to_show = '1';
	}
	if (
		((isset($matrix_info['config']['display_options']['are_same_option']) && $matrix_info['config']['display_options']['are_same_option'] == '1'))
		&& ((isset($matrix_info['mode']) && $matrix_info['mode'] == 'between_different_language_tags'))
		&&
		$clients_to_show == '1'
	)
	{
		if ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '1')
		{
			$stored_cookies                   = $_COOKIE;
			$stored_cookies['stored_cookies'] = $stored_cookies;
			$actual_module                    = $matrix_info['actual_module'];
			$module_id                        = $matrix_info['module_id'];
		}
		elseif ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '0'
			&& $matrix_info['config']['experimental_options']['coordinated_task'] == '1')
		{
			$stored_cookies                = $_COOKIE;
			$matrix_info['stored_cookies'] = $stored_cookies;
		}

		$means_same_table[] = "<table width='100%'>";

		if ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '1')
		{
			$request_package             = JText::_('MOD_STOOLPIGEON_REQUEST_PACKAGE');
			$discard_diff_changes        = JText::_('MOD_STOOLPIGEON_DISCART_DIFF_CHANGES');
			$discard_coordinated_changes = JText::_('MOD_STOOLPIGEON_DISCART_COORDINATED_CHANGES');
			if ($matrix_info['config']['experimental_options']['coordinated_task'] == '1')
			{
				$means_same_table[] = "<tr class='type4'><th colspan='3'>
			<input type='button' value='$request_package' onClick='request_pack()'>
			<input type='button' value='$discard_diff_changes' onClick='request_discart_diff_tags()'>
			<input type='button' value='$discard_coordinated_changes' onClick='request_discart_coordinated_task()'></th></tr></th></tr>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_EDIT') . "</td></tr>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_STORE') . "</td></tr>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_RESTORE') . "</td></tr>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_NOTE') . "</td></tr>";

			}
			else
			{
				$means_same_table[] = "<tr class='type4'><th colspan='3'>
			<input type='button' value='$request_package' onClick='request_pack()'>
			<input type='button' value='$discard_diff_changes' onClick='request_discart_diff_tags()'>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_EDIT') . "</td></tr>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_STORE') . "</td></tr>
			<tr><td colspan='3'>" . JText::_('MOD_STOOLPIGEON_EDIT_MODE_RESTORE') . "</td></tr>";
			}
		}

		$means_same_table[] = "<tr class='type4'><th colspan='3'><p>" . JText::_('MOD_STOOLPIGEON_MEANS_THE_SAME_Q') . "</p></th></tr>";

		$clients_availables = array('admin', 'site', 'installation');
		foreach ($clients_availables as $client_available)
		{
			if (isset($matrix_info['config']['client_selection'][$client_available]))
			{
				$clients[] = $client_available;
			}
		}


		foreach ($clients as $client)
		{
			$to_revise                   = array();
			$comming_from                = JText::_('MOD_STOOLPIGEON_COMMING_FROM_CT');
			$coordinated_diff_avaialable = '0';

			if ($matrix_info['config']['client_selection'][$client] == '1')
			{

				if ($matrix_info['config']['experimental_options']['coordinated_task'] == '1'
					&& isset($stored_cookies['coordinated_task'][$matrix_info['config']['source_language_tag']]
						[$matrix_info['config']['version_options']['source_version']][$client]))
				{
					$target_language_tag = $matrix_info['config']['target_language_tag'];
					$to_revise           = $stored_cookies['coordinated_task']
					[$matrix_info['config']['source_language_tag']][$matrix_info['config']['version_options']['source_version']][$client];

					foreach ($to_revise as $file_name => $data)
					{

						foreach ($data['keys'] as $source_key => $key_text)
						{

							$target_text = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
							[$file_name]['keys'][$source_key];

							$coordinated_target_version = $stored_cookies['coordinated_task']
							[$matrix_info['config']['source_language_tag']]
							[$matrix_info['config']['version_options']['source_version']][$client]
							[$file_name]['info'][$source_key];

							$comming_from = JText::_('MOD_STOOLPIGEON_COMMING_FROM_CT');

							$matrix_info['messages']['notes']['mandatory_target_ini_files'][$client]['are_same']
							[$target_language_tag . '.' . $file_name][] = $key_text . ",|," . $source_key . ",|,"
								. $target_text
								. ",|," . $comming_from;
							unset ($source_key, $key_text);
						}
						unset ($file_name, $data);
					}
				}

				if (isset($matrix_info['messages']['notes']['mandatory_target_ini_files'][$client]['are_same']))
				{
					$are_same_in_client_files = $matrix_info['messages']['notes']['mandatory_target_ini_files'][$client]['are_same'];

				}
				else
				{
					$are_same_in_client_files = array(JText::_('MOD_STOOLPIGEON_ALL_THE_INI_FILES') => array("---,|,"
						. JText::_('MOD_STOOLPIGEON_NOTHING_TO_SHOW') . ",|,---"));
				}

				$means_same_table[] = "<tr><td class='type4' colspan='3'>"
					. JText::_('MOD_STOOLPIGEON_' . strtoupper($client) . '_ZONE') . "</td></tr>";

				foreach ($are_same_in_client_files as $file_name => $are_same_in_client_file)
				{
					$means_same_table[] = "<tr><td class='type3' colspan='3'>" . $file_name
						. " - V: " . $matrix_info['config']['version_options']['target_version'] . "</td></tr>";

					$altern_style        = '0';
					$edited_altern_style = '0';

					foreach ($are_same_in_client_file as $keys)
					{
						$parts = explode(',|,', $keys);

						//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
						//? $parts[0] = htmlspecialchars ($parts[0]) : $parts[0];
						$parts[0] = htmlspecialchars($parts[0]);
						//Keys never must be html unscaped only the text.
						$parts[1] = htmlspecialchars($parts[1]);

						//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
						//? $parts[2] = htmlspecialchars ($parts[2]) : $parts[2];
						$parts[2]      = htmlspecialchars($parts[2]);
						$original_text = $parts[2];
						//single quotes issue breaking JS code when is passed from PHP is solved with workarround
						$original_text = str_replace("'", "MSP_SINGLE_QUOTES", $original_text);


						$coordinated_diff_available = '0';


						if ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '1'
							&& $parts[1] != JText::_('MOD_STOOLPIGEON_NOTHING_TO_SHOW'))
						{

							$catched_cookie_value = '';
							$catched_cookie_value = diff_tags_get_cookie_key($stored_cookies,
								$client, $actual_module, $file_name, $parts[1]);

							$id_ref   = "tag_module_" . $module_id . "_" . $client . "_" . $file_name . "_" . $parts[1];
							$id_ref_b = "text_module_" . $module_id . "_" . $client . "_" . $file_name . "_" . $parts[1];

							if ($parts[3] == $comming_from)
							{
								$parts[3] = "<font color='red'><b>" . $comming_from . "</b></font> ["
									. $matrix_info['config']['source_language_tag'] . " "
									. $matrix_info['config']['version_options']['source_version']
									. " vs " . $coordinated_target_version . "]";

								if ($matrix_info['config']['display_options']['display_diff'] == '1')
								{
									$source_language_tag = $matrix_info['config']['source_language_tag'];
									$target_language_tag = $matrix_info['config']['target_language_tag'];
									$source_version      = $matrix_info['config']['version_options']['source_version'];
									$diff_key            = $parts[1];

									$tag_len   = strlen($target_language_tag) + 1;
									$chain_tag = (mb_substr($file_name, 0, $tag_len, 'UTF-8'));
									$chain_ext = substr($file_name, -4);

									if ($chain_tag == $target_language_tag . ".")
									{
										$untagged_file = substr($file_name, $tag_len);
									}

									if (isset($matrix_info['stored_cookies']['coordinated_task']
										[$source_language_tag][$source_version][$client][$untagged_file]
										['diff'][$diff_key]))
									{
										$coordinated_diff            = $matrix_info['stored_cookies']['coordinated_task']
										[$source_language_tag][$source_version][$client][$untagged_file]
										['diff'][$diff_key];
										$coordinated_diff_avaialable = '1';
									}
									else
									{
										$coordinated_diff_avaialable = '0';
									}
								}
								else
								{
									$coordinated_diff_avaialable = '0';
								}
							}
							else
							{
								$coordinated_diff_avaialable = '0';
							}

							if ($catched_cookie_value != '' && $catched_cookie_value != $original_text)
							{
								$means_same_table[] = "<tr><td class='altern_$altern_style' rowspan='6'>"
									. $parts[1] . "</td><td class='source_text' colspan='2'>"
									. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . "</td></tr><tr><td colspan='2'>"
									. $parts[0] . "</td></tr><tr><td class='target_text' colspan='2'>"
									. JText::_('MOD_STOOLPIGEON_TARGET_TEXT_EDITABLE')
									. " " . $parts[3]
									. "</td></tr><tr><td colspan='2'><p class='editable' rel_rows = '6' rel_id='module_"
									. $module_id . "' rel_zone='" . $client . "' rel_file='" . $file_name . "' rel_key='"
									. $parts[1] . "' rel_value='" . $parts[2] . "' rel_original='" . $original_text . "'>"
									. $parts[2] . "</p></td></tr><td class='edited_text' colspan='2' id='"
									. $id_ref . "'>" . JText::_('MOD_STOOLPIGEON_EDITED_TEXT')
									. "</td></tr><tr><td colspan='2' id='" . $id_ref_b . "'>"
									. $catched_cookie_value . "</td></tr>";

							}
							else
							{

								$means_same_table[] = "<tr><td class='altern_$altern_style' rowspan='5'>"
									. $parts[1] . "</td><td class='source_text' colspan='2'>"
									. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . "</td></tr><tr><td colspan='2'>"
									. $parts[0] . "</td></tr><tr><td class='target_text' colspan='2'>"
									. JText::_('MOD_STOOLPIGEON_TARGET_TEXT_EDITABLE')
									. " " . $parts[3]
									. "</td></tr><tr><td colspan='2'><p class='editable' rel_rows = '5' rel_id='module_"
									. $module_id . "' rel_zone='" . $client . "' rel_file='" . $file_name . "' rel_key='"
									. $parts[1] . "' rel_value='" . $parts[2] . "' rel_original='" . $original_text . "'>"
									. $parts[2] . "</p></td></tr><td class='untranslated_text' colspan='2' id='"
									. $id_ref . "'>INITIAL STATE: UNTRANSLATED</td></tr>";

							}

							($edited_altern_style == '0') ? $edited_altern_style = '1' : $edited_altern_style = '0';

						}
						elseif (($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '0'
								&& $matrix_info['config']['experimental_options']['coordinated_task'] == '0')
							|| $parts[1] == JText::_('MOD_STOOLPIGEON_NOTHING_TO_SHOW'))
						{
							$means_same_table[] = "<tr><td class='altern_$altern_style' rowspan='4'>"
								. $parts[1] . "</td><td class='source_text' colspan='2'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . "</td></tr><tr><td colspan='2'>"
								. $parts[0] . "</td></tr><tr><td class='target_text' colspan='2'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . "</td></tr><tr><td colspan='2'>"
								. $parts[2] . "</td></tr>";

						}
						elseif ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '0'
							&& $matrix_info['config']['experimental_options']['coordinated_task'] == '1'
							&& $parts[3] == $comming_from
						)
						{

							if ($matrix_info['config']['display_options']['display_diff'] == '1')
							{
								$source_language_tag = $matrix_info['config']['source_language_tag'];
								$target_language_tag = $matrix_info['config']['target_language_tag'];
								$source_version      = $matrix_info['config']['version_options']['source_version'];
								$diff_key            = $parts[1];

								$tag_len   = strlen($target_language_tag) + 1;
								$chain_tag = (mb_substr($file_name, 0, $tag_len, 'UTF-8'));
								$chain_ext = substr($file_name, -4);

								if ($chain_tag == $target_language_tag . ".")
								{
									$untagged_file = substr($file_name, $tag_len);
								}

								if (isset($matrix_info['stored_cookies']['coordinated_task']
									[$source_language_tag][$source_version][$client][$untagged_file]
									['diff'][$diff_key]))
								{
									$coordinated_diff            = $matrix_info['stored_cookies']['coordinated_task']
									[$source_language_tag][$source_version][$client][$untagged_file]
									['diff'][$diff_key];
									$coordinated_diff_avaialable = '1';
								}
								else
								{
									$coordinated_diff_avaialable = '0';
								}

							}
							else
							{
								$coordinated_diff_avaialable = '0';
							}

							$means_same_table[] = "<tr><td class='altern_$altern_style' rowspan='4'>"
								. $parts[1] . "</td><td class='source_text' colspan='2'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT')
								. "<font color='red'><b>" . JText::_('MOD_STOOLPIGEON_COMMING_FROM_CT')
								. "</b></font> [" . $matrix_info['config']['source_language_tag'] . " "
								. $matrix_info['config']['version_options']['source_version']
								. " vs " . $coordinated_target_version . "]</td></tr><tr><td colspan='2'>"
								. $parts[0] . "</td></tr><tr><td class='target_text' colspan='2'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . "</td></tr><tr><td colspan='2'>"
								. $parts[2] . "</td></tr>";

						}
						elseif ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '0'
							&& $matrix_info['config']['experimental_options']['coordinated_task'] == '1'
							&& $parts[3] != $comming_from
						)
						{
							$means_same_table[]          = "<tr><td class='altern_$altern_style' rowspan='4'>"
								. $parts[1] . "</td><td class='source_text' colspan='2'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . "</td></tr><tr><td colspan='2'>"
								. $parts[0] . "</td></tr><tr><td class='target_text' colspan='2'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . "</td></tr><tr><td colspan='2'>"
								. $parts[2] . "</td></tr>";
							$coordinated_diff_avaialable = '0';
						}
						if ($coordinated_diff_avaialable == '1')
						{
							$means_same_table[] = "<tr><td class='diff' colspan='14'>"
								. $coordinated_diff . "</td></tr>";
						}

						($altern_style == '0') ? $altern_style = '1' : $altern_style = '0';
						unset($keys);
					}
					unset($file_name, $are_same_in_client_file);
				}


			}
			elseif ($matrix_info['config']['client_selection'][$client] == '2')
			{
				$means_same_table[] = "<p class='alert'>"
					. JText::_('MOD_STOOLPIGEON_SOMETHING_WRONG_' . strtoupper($client) . '_CONFIG') . "</p>";
			}
			unset($client);
		}//end foreach


		$means_same_table[] = "<tr class='type4'><th colspan='3'><p>"
			. JText::_('MOD_STOOLPIGEON_ONE_TIME_SOLVED_THE_CONFLICTIVE')
			. "</p></th></tr><tr><td colspan='3'>";

		if ($matrix_info['means_the_same'])
		{
			$means_same_table[] = $matrix_info['means_the_same'] . "</td></tr>";

		}
		else
		{

			$means_same_table[] = JText::_('MOD_STOOLPIGEON_WITHOUT_MTSK_TO_DISPLAY') . "</td></tr>";
		}

		$means_same_table[] = "<tr class='type4'><th colspan='3'><p>"
			. JText::_('MOD_STOOLPIGEON_IF_YOU_FOUND_KEYS') . "</p></th></tr><tr><td colspan='3'>";

		if ($matrix_info['means_the_same_errors'])
		{
			$means_same_table[] = $matrix_info['means_the_same_errors'] . "</td></tr>";

		}
		else
		{

			$means_same_table[] = "<b><font style='font-size: 12px;'>"
				. JText::_('MOD_STOOLPIGEON_WITHOUT_CONFLICTIVE_KEYS_TO_DISPLAY') . "</font></b></td></tr>";
		}

		$means_same_table[] = "</table>";

	}
	elseif (($matrix_info['config']['display_options']['are_same_option'] == '1')
		&& ($matrix_info['config']['client_selection']['admin'] == '2'
			|| $matrix_info['config']['client_selection']['site'] == '2'
			|| $matrix_info['config']['client_selection']['installation'] == '2'))
	{

		$means_same_table[] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_SHOW_SAME_SELECTED_ERROR') . "</p>";

	}
	elseif (($matrix_info['config']['display_options']['are_same_option'] == '1' && $matrix_info['mode'] == 'between_equal_language_tags'))
	{

		$means_same_table[] = "<p class='info'>" . JText::_('MOD_STOOLPIGEON_FOR_DISPLAY_THE_SAME_INFO') . "</p>";
	}

}//end display_means_same_table function

function display_zone_info(&$matrix_info, &$clients_to_display)
{

	//&& $matrix_info['mode'] == 'between_different_language_tags' == '1'
	//$scape_html = $matrix_info['config']['experimental_options']['scape_html'];
	$hide_tables          = $matrix_info['config']['display_options']['hide_tables'];
	$display_diff         = $matrix_info['config']['display_options']['display_diff'];
	$display_catched      = $matrix_info['config']['display_options']['display_catched'];
	$hide_keys_amount     = $matrix_info['config']['display_options']['hide_keys_amount'];
	$hide_mandatory_info  = $matrix_info['config']['display_options']['hide_mandatory_info'];
	$hide_source_info     = $matrix_info['config']['display_options']['hide_source_info'];
	$hide_target_info     = $matrix_info['config']['display_options']['hide_target_info'];
	$source_version       = $matrix_info['config']['version_options']['source_version'];
	$target_version       = $matrix_info['config']['version_options']['target_version'];
	$catch_quotes         = $matrix_info['config']['version_options']['catch_quotes'];
	$display_package_link = $matrix_info['config']['experimental_options']['display_package_link'];

	$bom_as_system_message               = $matrix_info['config']['system_messages_options']['bom_as_system_message'];
	$extra_space_as_system_message       = $matrix_info['config']['system_messages_options']['extra_space_as_system_message'];
	$changed_keys_text_as_system_message = $matrix_info['config']['system_messages_options']['missed_quotes_as_system_message'];
	$bad_usage_quotes_as_system_message  = $matrix_info['config']['system_messages_options']['bad_usage_quotes_as_system_message'];
	$missed_equal_as_system_message      = $matrix_info['config']['system_messages_options']['missed_equal_as_system_message'];
	$missed_keys_as_system_message       = $matrix_info['config']['system_messages_options']['missed_keys_as_system_message'];
	$changed_keys_text_as_system_message = $matrix_info['config']['system_messages_options']['changed_keys_text_as_system_message'];

	$source_language_tag = $matrix_info['config']['source_language_tag'];
	$target_language_tag = $matrix_info['config']['target_language_tag'];


	$relative_target_line       = $matrix_info['config']['experimental_options']['relative_target_line'];
	$synchronise_target_files   = $matrix_info['config']['experimental_mode']['synchronise_target_files'];
	$enable_edit_mode           = $matrix_info['config']['experimental_mode']['enable_edit_mode'];
	$coordinated_task           = $matrix_info['config']['experimental_options']['coordinated_task'];
	$backup_target_files        = $matrix_info['config']['experimental_mode']['backup_target_files'];
	$sort_target_keys           = $matrix_info['config']['experimental_mode']['sort_target_keys'];
	$source_admin_folder        = $matrix_info['config']['paths']['saf'];
	$target_admin_folder        = $matrix_info['config']['paths']['taf'];
	$source_site_folder         = $matrix_info['config']['paths']['ssf'];
	$target_site_folder         = $matrix_info['config']['paths']['tsf'];
	$source_installation_folder = $matrix_info['config']['paths']['sif'];
	$target_installation_folder = $matrix_info['config']['paths']['tif'];


	$locations          = array('source', 'target');
	$clients_availables = array('admin', 'site', 'installation');
	foreach ($clients_availables as $client_available)
	{
		if (isset($matrix_info['config']['client_selection'][$client_available]))
		{
			$clients[] = $client_available;
		}
	}

	foreach ($clients as $client)
	{

		if ($matrix_info['config']['client_selection'][$client] == '1')

		{

			if ($client == 'admin')
			{
				$upper_client                     = 'ADMIN';
				$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
				$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
				$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
				$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');


			}
			elseif ($client == 'site')
			{
				$upper_client                     = 'SITE';
				$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
				$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
				$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
				$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');

			}
			elseif ($client == 'installation')
			{
				$upper_client                     = 'INSTALLATION';
				$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
				$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
				$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
				$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');
			}


			$there_are_target_incidences = '0';
			$there_are_source_incidences = '0';
			$matrix_info['extra_files']  = array();


			$clients_to_display[$client] [] = "<p></p><h1>" . JText::_('MOD_STOOLPIGEON_' . strtoupper($client) . '_ZONE') . "</h1>";

			if ($hide_mandatory_info == '0')
			{

				$clients_to_display[$client] [] = "<p></p><h3>" . JText::_('MOD_STOOLPIGEON_CONFIGURATION_INFO') . "</h3>";
				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_ARE_COMPARING_THE_SOURCE')
					. "<b>" . $source_language_tag . JText::_('MOD_STOOLPIGEON_VERSION_B')
					. $source_version . JText::_('MOD_STOOLPIGEON_VERSION_E') . "</b>"
					. JText::_('MOD_STOOLPIGEON_AGAINST_THE_TARGET_LANGUAGE_TAG')
					. "<b>" . $target_language_tag . JText::_('MOD_STOOLPIGEON_VERSION_B')
					. $target_version . JText::_('MOD_STOOLPIGEON_VERSION_E') . "</b></p>";

				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_AMOUNT_OF_MANDATORY_FILES')
					. "<b>" . $total_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

				$clients_to_display[$client] [] = "<p><b>" . $total_ini_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_ARE_TYPE_INI_PATTERN') . "</p>";

				$clients_to_display[$client] [] = "<p><b>" . $total_non_ini_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_ARE_TYPE_NON_INI_NO_TAG_PATTERN') . "</p>";

				$clients_to_display[$client] [] = "<p><b>" . $total_non_ini_tagged_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_ARE_TYPE_NON_INI_WITH_TAG_PATTERN') . "</p>";


				foreach ($locations as $location)
				{

					if ($client == 'admin' && $location == 'source')
					{
						$client_base_path = $source_admin_folder;
						$upper_location   = 'SOURCE';
						$language_tag     = $source_language_tag;

					}
					elseif ($client == 'admin' && $location == 'target')
					{
						$client_base_path = $target_admin_folder;
						$upper_location   = 'TARGET';
						$language_tag     = $target_language_tag;

					}
					elseif ($client == 'site' && $location == 'source')
					{
						$client_base_path = $source_site_folder;
						$upper_location   = 'SOURCE';
						$language_tag     = $source_language_tag;

					}
					elseif ($client == 'site' && $location == 'target')
					{
						$client_base_path = $target_site_folder;
						$upper_location   = 'TARGET';
						$language_tag     = $target_language_tag;

					}
					elseif ($client == 'installation' && $location == 'source')
					{
						$client_base_path = $source_installation_folder;
						$upper_location   = 'SOURCE';
						$language_tag     = $source_language_tag;

					}
					elseif ($client == 'installation' && $location == 'target')
					{
						$client_base_path = $target_installation_folder;
						$upper_location   = 'TARGET';
						$language_tag     = $target_language_tag;
					}

					$path                                                = $client_base_path . '/';
					$all_files                                           = JFolder::files($path);
					$matrix_info['all_client_files'][$client][$location] = $all_files;
					$slt                                                 = $matrix_info['config']['source_language_tag'];
					$tlt                                                 = $matrix_info['config']['target_language_tag'];


					if ($client == 'admin'
						&& $location == 'target'
						&& !empty($matrix_info['admin_files_to_keep_in_target'])
						&& ($slt == 'en-GB' && $tlt != 'en-GB'))
					{
						$all_target_admin_files = $matrix_info['all_client_files']['admin']['target'];

						foreach ($matrix_info['admin_files_to_keep_in_target'] as $aftk)
						{
							if (!in_array($tlt . '.' . $aftk, $all_target_admin_files))
							{
								$msg = "<br /><font color='#000033'>ADMIN FILE TO KEEP IN TARGET NOT PRESENT: "
									. $tlt . "." . $aftk
									. "</font><br />";
								JError::raiseWarning(0, $msg);
							}

						}

					}
					elseif ($client == 'site'
						&& $location == 'target'
						&& !empty($matrix_info['site_files_to_keep_in_target'])
						&& ($slt == 'en-GB' && $tlt != 'en-GB'))
					{
						$all_target_site_files = $matrix_info['all_client_files']['site']['target'];

						foreach ($matrix_info['site_files_to_keep_in_target'] as $sftk)
						{
							if (!in_array($tlt . '.' . $sftk, $all_target_site_files))
							{
								$msg = "<br /><font color='#000033'>SITE FILE TO KEEP IN TARGET NOT PRESENT: "
									. $tlt . "." . $sftk
									. "</font><br />";
								JError::raiseWarning(0, $msg);
							}

						}
					}

					foreach ($all_files as $file)
					{
						$maximo    = strlen($file);
						$tag_len   = strlen($language_tag) + 1;
						$chain_tag = (mb_substr($file, 0, $tag_len, 'UTF-8'));
						$chain_ext = substr($file, -4);

						if ($chain_tag == $language_tag . ".")
						{
							$file_name = substr($file, $tag_len);
							$untagged  = '1';
						}
						else
						{
							$file_name = $file;
							$untagged  = '0';
						}

						if ((!in_array($file_name, $matrix_info['mandatories'][$client . '_ini_files']))
							&& (isset($matrix_info['mandatories'][$client . '_non_ini_files'])
								&& !in_array($file_name, $matrix_info['mandatories'][$client . '_non_ini_files']))
							&& (!in_array($file_name, $matrix_info['mandatories'][$client . '_non_ini_tagged_files'])))
						{


							if ($untagged == '1' && $chain_ext == '.ini')
							{
								if ($client == 'admin'
									&& $location == 'target'
									&& !empty($matrix_info['admin_files_to_keep_in_target'])
									&& in_array($file_name, $matrix_info['admin_files_to_keep_in_target'])
									&& ($slt == 'en-GB' && $tlt != 'en-GB'))
								{
									$matrix_info['extra_files_to_keep'][$location][$client . '_ini_files'][] = $file_name . " ";
								}
								elseif ($client == 'site'
									&& $location == 'target'
									&& !empty($matrix_info['site_files_to_keep_in_target'])
									&& in_array($file_name, $matrix_info['site_files_to_keep_in_target'])
									&& ($slt == 'en-GB' && $tlt != 'en-GB'))
								{
									$matrix_info['extra_files_to_keep'][$location][$client . '_ini_files'][] = $file_name . " ";
								}
								else
								{
									$matrix_info['extra_files'][$location][$client . '_ini_files'][] = $file_name . ",|, ";
									$matrix_info['extra_files'][$location]['all'][]                  = "<font color='red'>" . $file . "</font> ";
								}

							}
							elseif ($untagged == '0' && $chain_ext !== '.ini')
							{
								if ($file_name == 'index.html')
								{
									$matrix_info['extra_files'][$location]['all'][] = "<font color='green'>" . $file . "</font> ";
								}
								else
								{
									$matrix_info['extra_files'][$location][$client . '_non_ini_files'][] = $file_name . ",|, ";
									$matrix_info['extra_files'][$location]['all'][]                      = "<font color='red'>" . $file . "</font> ";
								}

							}
							elseif ($untagged == '1' && $chain_ext !== '.ini')
							{
								$matrix_info['extra_files'][$location][$client . '_non_ini_tagged_files'][] = $file_name . ",|, ";
								$matrix_info['extra_files'][$location]['all'][]                             = "<font color='red'>" . $file . "</font> ";

							}
							else
							{
								$matrix_info['extra_files'][$location][$client . 'rare_files'][] = $file_name . ",|, ";
								$matrix_info['extra_files'][$location]['all'][]                  = "<font color='red'>" . $file . "</font> ";
							}

						}
						unset($file);
					}
					unset($location);
				}//end foreach locations


				foreach ($locations as $location)
				{
					if ($location == 'target' && $client == 'admin')
					{
						if (isset($matrix_info['extra_files_to_keep']['target']['admin_ini_files']))
						{
							$extra_files_to_keep = '';

							foreach ($matrix_info['extra_files_to_keep']['target']['admin_ini_files'] as $extra_to_keep)
							{
								$extra_files_to_keep .= "<font color='green'>" . $tlt . '.' . $extra_to_keep . "</font><br />";
								unset($extra_to_keep);
							}
							$clients_to_display[$client] [] = "<h2>FILES TO KEEP IN TARGET PRESENTS"
								. " | "
								. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";

							$clients_to_display[$client] [] = "<p><b>This files are not present at the source client, but are required as files to keep in target by the module configuration</b>. This one also means that this files must be present in the 'install.xml' file related with this client.</p>";

							$clients_to_display[$client] [] = "<p>" . $extra_files_to_keep . "</p>";

						}
					}

					if ($location == 'target' && $client == 'site')
					{
						if (isset($matrix_info['extra_files_to_keep']['target']['site_ini_files']))
						{
							$extra_files_to_keep = '';

							foreach ($matrix_info['extra_files_to_keep']['target']['site_ini_files'] as $extra_to_keep)
							{
								$extra_files_to_keep .= "<font color='green'>" . $tlt . '.' . $extra_to_keep . "</font><br />";
								unset($extra_to_keep);
							}
							$clients_to_display[$client] [] = "<h2>FILES TO KEEP IN TARGET PRESENTS"
								. " | "
								. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";

							$clients_to_display[$client] [] = "<p><b>This files are not present at the source client, but are required as files to keep in target by the module configuration</b>. This one also means that this files must be present in the 'install.xml' file related with this client.</p>";

							$clients_to_display[$client] [] = "<p>" . $extra_files_to_keep . "</p>";

						}
					}

					if (isset($matrix_info['extra_files'][$location]['all']))
					{
						$extra_files = '';

						foreach ($matrix_info['extra_files'][$location]['all'] as $extra)
						{
							if ($extra == "<font color='green'>index.html</font> ")
							{
								$extra_files .= $extra . JText::_('MOD_STOOLPIGEON_NORMAL_HTML_CASE') . "<br />";
							}
							else
							{
								$extra_files .= $extra . '<br />';
							}
							unset($extra);
						}


						$clients_to_display[$client] [] = "<h2>" . JText::_('MOD_STOOLPIGEON_EXTRA_FILES_IN_'
								. strtoupper($location) . '_DIRECTORY') . " | "
							. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";

						$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_EXTRA_'
								. strtoupper($location) . '_FILES') . "</p>";

						$clients_to_display[$client] [] = "<p>" . $extra_files . "</p>";
						$clients_to_display[$client] [] = "<p class='info'>" . JText::_('MOD_STOOLPIGEON_IF_ALL_IS_OK') . "</p>";
						$clients_to_display[$client] [] = "<p class='tip'>" . JText::_('MOD_STOOLPIGEON_IF_YOU_NEED_INCLUDE') . "</p>";


						if (isset($matrix_info['extra_files'][$location][$client . '_ini_files']))
						{

							$extra_ti_files = '';

							foreach ($matrix_info['extra_files'][$location][$client . '_ini_files'] as $extra_ti)
							{
								$extra_ti_files .= $extra_ti;
								unset($extra_ti);
							}
							$clients_to_display[$client][] = "<p><b>" . JText::_('MOD_STOOLPIGEON_TAGGED_TYPE_INI') . "</b>"
								. $extra_ti_files . "</p>";
						}

						if (isset($matrix_info['extra_files'][$location][$client . '_non_ini_files']))
						{

							$extra_tni_files = '';
							foreach ($matrix_info['extra_files'][$location][$client . '_non_ini_files'] as $extra_tni)
							{
								$extra_tni_files .= $extra_tni;
								unset($extra_tni);
							}
							$clients_to_display[$client][] = "<p><b>" . JText::_('MOD_STOOLPIGEON_TAGGED_TYPE_NON_INI') . "</b>"
								. $extra_tni_files . "</p>";
						}

						if (isset($matrix_info['extra_files'][$location][$client . '_non_ini_tagged_files']))
						{

							$extra_tnit_files = '';
							foreach ($matrix_info['extra_files'][$location][$client . '_non_ini_tagged_files'] as $extra_tnit)
							{
								$extra_tnit_files .= $extra_tnit;
								unset($extra_tnit);
							}
							$clients_to_display[$client][] = "<p><b>" . JText::_('MOD_STOOLPIGEON_UNTAGGED_TYPE_NON_INI') . "</b>"
								. $extra_tnit_files . "</p>";
						}

						if (isset($matrix_info['mandatories'][$client . '_rare_files']))
						{

							$rare_files = '';
							foreach ($matrix_info['extra_files'][$location]['rare_files'] as $rare_file)
							{
								$rare_files .= $rare_file;
								unset($rare_file);
							}
							$clients_to_display[$client][] = "<p><b>" . JText::_('MOD_STOOLPIGEON_TYPE_RARE') . "</b>"
								. $rare_files . "</p>";
						}


						$clients_to_display[$client][] = "<p class='alert'><b>"
							. JText::_('MOD_STOOLPIGEON_THE_PROGRAM_CANNOT_DETERMINE') . "</b></p>";
					}
					unset($location);
				}
				$clients_to_display[$client][] = "<span class='block_separator'></span>";
			} //end of hide_mandatory_info ==0


			if ($hide_source_info == '0')
			{
				$total_location_files = '0';

				if ($client == 'admin')
				{
					$upper_client                     = 'ADMIN';
					$upper_location                   = 'SOURCE';
					$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
					$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
					$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
					$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');
					$client_base_path                 = $source_admin_folder;
					$language_tag                     = $source_language_tag;

					if (isset($matrix_info['file_existent'][$client . '_ini_files']['source']['files']))
					{
						$total_ini_files = count($matrix_info['file_existent'][$client . '_ini_files']['source']['files']);
						$ini_files       = $matrix_info['file_existent'][$client . '_ini_files']['source']['files'];
					}
					else
					{
						$total_ini_files = '0';
						$ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_files']['source']['files']))
					{
						$total_non_ini_files = count($matrix_info['file_existent'][$client . '_non_ini_files']['source']['files']);
						$non_ini_files       = $matrix_info['file_existent'][$client . '_non_ini_files']['source']['files'];
					}
					else
					{
						$total_non_ini_files = '0';
						$non_ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files']))
					{
						$total_non_ini_tagged_files = count($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files']);
						$non_ini_tagged_files       = $matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files'];
					}
					else
					{
						$total_non_ini_tagged_files = '0';
						$non_ini_tagged_files       = array();
					}

					$total_location_files = $total_ini_files + $total_non_ini_files + $total_non_ini_tagged_files;

					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['source']))
					{
						$inexistent_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['source'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['source']))
					{
						$inexistent_non_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['source'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['source']))
					{
						$inexistent_non_ini_tagged_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['source'];
					}

				}
				elseif ($client == 'site')
				{

					$upper_client                     = 'SITE';
					$upper_location                   = 'SOURCE';
					$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
					$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
					$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
					$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');
					$client_base_path                 = $source_site_folder;
					$language_tag                     = $source_language_tag;

					if (isset($matrix_info['file_existent'][$client . '_ini_files']['source']['files']))
					{
						$total_ini_files = count($matrix_info['file_existent'][$client . '_ini_files']['source']['files']);
						$ini_files       = $matrix_info['file_existent'][$client . '_ini_files']['source']['files'];
					}
					else
					{
						$total_ini_files = '0';
						$ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_files']['source']['files']))
					{
						$total_non_ini_files = count($matrix_info['file_existent'][$client . '_non_ini_files']['source']['files']);
						$non_ini_files       = $matrix_info['file_existent'][$client . '_non_ini_files']['source']['files'];
					}
					else
					{
						$total_non_ini_files = '0';
						$non_ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files']))
					{
						$total_non_ini_tagged_files = count($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files']);
						$non_ini_tagged_files       = $matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files'];
					}
					else
					{
						$total_non_ini_tagged_files = '0';
						$non_ini_tagged_files       = array();
					}

					$total_location_files = $total_ini_files + $total_non_ini_files + $total_non_ini_tagged_files;;

					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['source']))
					{
						$inexistent_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['source'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['source']))
					{
						$inexistent_non_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['source'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['source']))
					{
						$inexistent_non_ini_tagged_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['source'];
					}

				}
				elseif ($client == 'installation')
				{
					$upper_client                     = 'INSTALLATION';
					$upper_location                   = 'SOURCE';
					$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
					$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
					$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
					$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');
					$client_base_path                 = $source_installation_folder;
					$language_tag                     = $source_language_tag;

					if (isset($matrix_info['file_existent'][$client . '_ini_files']['source']['files']))
					{
						$total_ini_files = count($matrix_info['file_existent'][$client . '_ini_files']['source']['files']);
						$ini_files       = $matrix_info['file_existent'][$client . '_ini_files']['source']['files'];
					}
					else
					{
						$total_ini_files = '0';
						$ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_files']['source']['files']))
					{
						$total_non_ini_files = count($matrix_info['file_existent'][$client . '_non_ini_files']['source']['files']);
						$non_ini_files       = $matrix_info['file_existent'][$client . '_non_ini_files']['source']['files'];
					}
					else
					{
						$total_non_ini_files = '0';
						$non_ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files']))
					{
						$total_non_ini_tagged_files = count($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files']);
						$non_ini_tagged_files       = $matrix_info['file_existent'][$client . '_non_ini_tagged_files']['source']['files'];
					}
					else
					{
						$total_non_ini_tagged_files = '0';
						$non_ini_tagged_files       = array();
					}

					$total_location_files = $total_ini_files + $total_non_ini_files + $total_non_ini_tagged_files;

					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['source']))
					{
						$inexistent_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['source'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['source']))
					{
						$inexistent_non_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['source'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['source']))
					{
						$inexistent_non_ini_tagged_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['source'];
					}

				}

				//source files details
				$clients_to_display[$client] [] = "<p></p><h3>" . JText::_('MOD_STOOLPIGEON_SOURCE_INFO') . "</h3>";
				$clients_to_display[$client] [] = "<p></p><h2>" . JText::_('MOD_STOOLPIGEON_SOURCE_FILES_DETAILS') . " | "
					. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";
				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_AMOUNT_OF_MANDATORY_SOURCE')
					. "<b>" . $client_base_path . "/" . "</b>" . JText::_('MOD_STOOLPIGEON_ARE') . "<b>" . $total_location_files
					. "</b>" . JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_HAVE') . "<b>" . $total_ini_files . "</b>"
					. JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_ini_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_TYPE_INI_YOU_MISSING') . "<b>" . ($total_ini_mandatories - $total_ini_files)
					. "</b>" . JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

				if (($total_mandatories - $total_location_files) > '0')
				{

					if (($total_ini_mandatories - $total_ini_files) > '0')
					{
						foreach ($inexistent_ini_files as $file => $name)
						{
							$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
								. "<font color='red'>" . $language_tag . "." . $name . "</font>"
								. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_SOURCE_PATH') . "</p>";
							unset($file, $name);
						}
					}

					$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_HAVE') . "<b>" . $total_non_ini_files
						. "</b>" . JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_non_ini_mandatories . "</b>"
						. JText::_('MOD_STOOLPIGEON_TYPE_NON_INI_NO_TAG_YOU_MISSING') . "<b>"
						. ($total_non_ini_mandatories - $total_non_ini_files) . "</b>"
						. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

					if (($total_non_ini_mandatories - $total_non_ini_files) > '0')
					{
						foreach ($inexistent_non_ini_files as $file => $name)
						{
							if ($name == 'install.xml' && $language_tag == 'en-GB')
							{
								$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
									. "<font color='darkorange'>" . $name . "</font>"
									. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_SOURCE_PATH')
									. JText::_('MOD_STOOLPIGEON_NORMAL_CASE')
									. "</p>";
							}
							else
							{
								$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
									. "<font color='red'>" . $name . "</font>"
									. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_SOURCE_PATH') . "</p>";
							}
							unset($file, $name);
						}
					}

					$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_HAVE') . "<b>"
						. $total_non_ini_tagged_files . "</b>"
						. JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_non_ini_tagged_mandatories . "</b>"
						. JText::_('MOD_STOOLPIGEON_TYPE_NON_INI_WITH_TAG_YOU_MISSING') . "<b>"
						. ($total_non_ini_tagged_mandatories - $total_non_ini_tagged_files) . "</b>"
						. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

					if (($total_non_ini_tagged_mandatories - $total_non_ini_tagged_files) > '0')
					{
						foreach ($inexistent_non_ini_tagged_files as $file => $name)
						{
							$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
								. "<font color='red'>" . $language_tag . "." . $name . "</font>"
								. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_SOURCE_PATH') . "</p>";
							unset($file, $name);
						}
					}

				} //$total_mandatories - $total_location_files

				$clients_to_display[$client] [] = "<p></p><h2>" . JText::_('MOD_STOOLPIGEON_SOURCE_KEYS_DETAILS') . " | "
					. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";

				$files_list  = '';
				$total_keys  = 0;
				$total_words = 0;

				foreach ($ini_files as $file => $name)
				{

					$keys_amount = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
					[$name]['counter']['keys_amount'];
					$file_words  = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
					[$name]['counter']['words'];

					$files_list .= "<i>" . $language_tag . "." . $name . "</i>"
						. JText::_('MOD_STOOLPIGEON_DEFENITIONS_P') . $keys_amount
						. JText::_('MOD_STOOLPIGEON_WORDS_P') . $file_words . ")  <br />";

					$total_keys  = $total_keys + $keys_amount;
					$total_words = $total_words + $file_words;

					unset($file, $name);
				}
				if ($hide_keys_amount == '0')
				{
					$clients_to_display[$client] [] = $files_list;
				}

				$clients_to_display[$client] [] = "<p><font style='font-size:15px;'>"
					. JText::_('MOD_STOOLPIGEON_TOTAL_AMOUNT_SOURCE_KEYS')
					. "</font> <b>" . $total_keys . "</b></p>";

				$clients_to_display[$client] [] = "<p><font style='font-size:15px;'>"
					. JText::_('MOD_STOOLPIGEON_TOTAL_AMOUNT_SOURCE_WORDS')
					. "</font> <b>" . $total_words . "</b></p>";

				$clients_to_display[$client] [] = "<span class='block_separator'></span>";


			}//end of hide_source_info ==0


			if ($hide_target_info == '0')
			{
				$total_location_files = '0';

				if ($client == 'admin')
				{
					$upper_client                     = 'ADMIN';
					$upper_location                   = 'TARGET';
					$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
					$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
					$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
					$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');
					$client_base_path                 = $target_admin_folder;
					$language_tag                     = $target_language_tag;

					if (isset($matrix_info['file_existent'][$client . '_ini_files']['target']['files']))
					{
						$total_ini_files = count($matrix_info['file_existent'][$client . '_ini_files']['target']['files']);
						$ini_files       = $matrix_info['file_existent'][$client . '_ini_files']['target']['files'];

					}
					else
					{
						$total_ini_files = '0';
						$ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_files']['target']['files']))
					{
						$total_non_ini_files = count($matrix_info['file_existent'][$client . '_non_ini_files']['target']['files']);
						$non_ini_files       = $matrix_info['file_existent'][$client . '_non_ini_files']['target']['files'];
					}
					else
					{
						$total_non_ini_files = '0';
						$non_ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files']))
					{
						$total_non_ini_tagged_files = count($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files']);
						$non_ini_tagged_files       = $matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files'];
					}
					else
					{
						$total_non_ini_tagged_files = '0';
						$non_ini_tagged_files       = array();
					}

					$total_location_files = $total_ini_files + $total_non_ini_files + $total_non_ini_tagged_files;

					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['target']))
					{
						$inexistent_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['target'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['target']))
					{
						$inexistent_non_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['target'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['target']))
					{
						$inexistent_non_ini_tagged_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['target'];
					}


				}
				elseif ($client == 'site')
				{

					$upper_client                     = 'SITE';
					$upper_location                   = 'TARGET';
					$total_mandatories                = get_mandatories($matrix_info, $client, $type_format = 'all', $required = 'count');
					$total_ini_mandatories            = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');
					$total_non_ini_mandatories        = get_mandatories($matrix_info, $client, $type_format = 'non_ini', $required = 'count');
					$total_non_ini_tagged_mandatories = get_mandatories($matrix_info, $client, $type_format = 'non_ini_tagged', $required = 'count');
					$client_base_path                 = $target_site_folder;
					$language_tag                     = $target_language_tag;

					if (isset($matrix_info['file_existent'][$client . '_ini_files']['target']['files']))
					{
						$total_ini_files = count($matrix_info['file_existent'][$client . '_ini_files']['target']['files']);
						$ini_files       = $matrix_info['file_existent'][$client . '_ini_files']['target']['files'];
						if (!empty($matrix_info['site_files_to_keep_in_target']))
						{

						}
					}
					else
					{
						$total_ini_files = '0';
						$ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_files']['target']['files']))
					{
						$total_non_ini_files = count($matrix_info['file_existent'][$client . '_non_ini_files']['target']['files']);
						$non_ini_files       = $matrix_info['file_existent'][$client . '_non_ini_files']['target']['files'];
					}
					else
					{
						$total_non_ini_files = '0';
						$non_ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files']))
					{
						$total_non_ini_tagged_files = count($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files']);
						$non_ini_tagged_files       = $matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files'];
					}
					else
					{
						$total_non_ini_tagged_files = '0';
						$non_ini_tagged_files       = array();
					}

					$total_location_files = $total_ini_files + $total_non_ini_files + $total_non_ini_tagged_files;

					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['target']))
					{
						$inexistent_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['target'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['target']))
					{
						$inexistent_non_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['target'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['target']))
					{
						$inexistent_non_ini_tagged_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['target'];
					}

				}
				elseif ($client == 'installation')
				{
					$upper_client          = 'INSTALLATION';
					$upper_location        = 'TARGET';
					$total_mandatories     = count($matrix_info['mandatories'][$client]);
					$total_ini_mandatories = count($matrix_info['mandatories'][$client
					. '_ini_files']);

					if (isset($matrix_info['mandatories'][$client . '_non_ini_files']))
					{
						$total_non_ini_mandatories = count($matrix_info['mandatories'][$client
						. '_non_ini_files']);
					}
					else
					{
						$total_non_ini_mandatories = 0;
					}

					$total_non_ini_tagged_mandatories = count($matrix_info['mandatories'][$client
					. '_non_ini_tagged_files']);
					$client_base_path                 = $target_installation_folder;
					$language_tag                     = $target_language_tag;

					if (isset($matrix_info['file_existent'][$client . '_ini_files']['target']['files']))
					{
						$total_ini_files = count($matrix_info['file_existent'][$client . '_ini_files']['target']['files']);
						$ini_files       = $matrix_info['file_existent'][$client . '_ini_files']['target']['files'];
					}
					else
					{
						$total_ini_files = '0';
						$ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_files']['target']['files']))
					{
						$total_non_ini_files = count($matrix_info['file_existent'][$client . '_non_ini_files']['target']['files']);
						$non_ini_files       = $matrix_info['file_existent'][$client . '_non_ini_files']['target']['files'];
					}
					else
					{
						$total_non_ini_files = '0';
						$non_ini_files       = array();
					}

					if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files']))
					{
						$total_non_ini_tagged_files = count($matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files']);
						$non_ini_tagged_files       = $matrix_info['file_existent'][$client . '_non_ini_tagged_files']['target']['files'];
					}
					else
					{
						$total_non_ini_tagged_files = '0';
						$non_ini_tagged_files       = array();
					}

					$total_location_files = $total_ini_files + $total_non_ini_files + $total_non_ini_tagged_files;

					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['target']))
					{
						$inexistent_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_ini_files']['target'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['target']))
					{
						$inexistent_non_ini_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_files']['target'];
					}
					if (isset($matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['target']))
					{
						$inexistent_non_ini_tagged_files = $matrix_info['incidences']['file_non_existent']
						[$client . '_non_ini_tagged_files']['target'];
					}

				}

				//target files details
				$clients_to_display[$client] [] = "<p></p><h3>" . JText::_('MOD_STOOLPIGEON_TARGET_INFO') . "</h3>";
				$clients_to_display[$client] [] = "<p></p><h2>" . JText::_('MOD_STOOLPIGEON_TARGET_FILES_DETAILS') . " | "
					. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";
				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_AMOUNT_OF_MANDATORY_TARGET')
					. "<b>" . $client_base_path . "/" . "</b>" . JText::_('MOD_STOOLPIGEON_ARE') . "<b>" . $total_location_files
					. "</b>" . JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_HAVE') . "<b>" . $total_ini_files . "</b>"
					. JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_ini_mandatories . "</b>"
					. JText::_('MOD_STOOLPIGEON_TYPE_INI_YOU_MISSING') . "<b>" . ($total_ini_mandatories - $total_ini_files)
					. "</b>" . JText::_('MOD_STOOLPIGEON_FILES') . "</p>";


				if (($total_mandatories - $total_location_files) > '0')
				{

					if (($total_ini_mandatories - $total_ini_files) > '0')
					{
						foreach ($inexistent_ini_files as $file => $name)
						{
							$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
								. "<font color='red'>" . $language_tag . "." . $name . "</font>"
								. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_TARGET_PATH') . "</p>";
							unset($file, $name);
						}
					}

					$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_HAVE') . "<b>" . $total_non_ini_files
						. "</b>" . JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_non_ini_mandatories . "</b>"
						. JText::_('MOD_STOOLPIGEON_TYPE_NON_INI_NO_TAG_YOU_MISSING') . "<b>"
						. ($total_non_ini_mandatories - $total_non_ini_files) . "</b>"
						. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

					if (($total_non_ini_mandatories - $total_non_ini_files) > '0')
					{
						foreach ($inexistent_non_ini_files as $file => $name)
						{
							if ($name == 'install.xml' && $language_tag == 'en-GB')
							{
								$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
									. "<font color='darkorange'>" . $name . "</font>"
									. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_TARGET_PATH')
									. JText::_('MOD_STOOLPIGEON_NORMAL_CASE')
									. "</p>";
							}
							else
							{
								$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
									. "<font color='red'>" . $name . "</font>"
									. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_TARGET_PATH') . "</p>";
							}
							unset($file, $name);
						}
					}

					$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_YOU_HAVE') . "<b>" . $total_non_ini_tagged_files . "</b>"
						. JText::_('MOD_STOOLPIGEON_OF') . "<b>" . $total_non_ini_tagged_mandatories . "</b>"
						. JText::_('MOD_STOOLPIGEON_TYPE_NON_INI_WITH_TAG_YOU_MISSING') . "<b>"
						. ($total_non_ini_tagged_mandatories - $total_non_ini_tagged_files) . "</b>"
						. JText::_('MOD_STOOLPIGEON_FILES') . "</p>";

					if (($total_non_ini_tagged_mandatories - $total_non_ini_tagged_files) > '0')
					{
						foreach ($inexistent_non_ini_tagged_files as $file => $name)
						{
							$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_THE_FILE')
								. "<font color='red'>" . $language_tag . "." . $name . "</font>"
								. JText::_('MOD_STOOLPIGEON_IS_MISSED_AT_TARGET_PATH') . "</p>";
							unset($file, $name);
						}
					}

				} //$total_mandatories - $total_location_files

				$clients_to_display[$client] [] = "<p></p><h2>" . JText::_('MOD_STOOLPIGEON_TARGET_KEYS_DETAILS') . " | "
					. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";

				$files_list  = '';
				$total_keys  = 0;
				$total_words = 0;

				foreach ($ini_files as $file => $name)
				{

					$keys_amount = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
					[$name]['counter']['keys_amount'];
					$file_words  = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
					[$name]['counter']['words'];

					$files_list .= "<i>" . $language_tag . "." . $name . "</i>"
						. JText::_('MOD_STOOLPIGEON_DEFENITIONS_P') . $keys_amount
						. JText::_('MOD_STOOLPIGEON_WORDS_P') . $file_words . ")  <br />";

					$total_keys  = $total_keys + $keys_amount;
					$total_words = $total_words + $file_words;
					unset($file, $name);
				}
				if ($hide_keys_amount == '0')
				{
					$clients_to_display[$client] [] = $files_list;
				}

				$clients_to_display[$client] [] = "<p><font style='font-size:15px;'>"
					. JText::_('MOD_STOOLPIGEON_TOTAL_AMOUNT_TARGET_KEYS')
					. "</font> <b>" . $total_keys . "</b></p>";

				$clients_to_display[$client] [] = "<p><font style='font-size:15px;'>"
					. JText::_('MOD_STOOLPIGEON_TOTAL_AMOUNT_TARGET_WORDS')
					. "</font> <b>" . $total_words . "</b></p>";

				$clients_to_display[$client] [] = "<span class='block_separator'></span>";


			}//end of hide_target_info ==0

			//COMPARABLE FILES

			$amount_inexistent_source_ini_files = get_files_inexistent($matrix_info, $client, $type_format = 'ini', $location = 'source', $required = 'count');
			$amount_inexistent_target_ini_files = get_files_inexistent($matrix_info, $client, $type_format = 'ini', $location = 'target', $required = 'count');
			$total_inexistent_ini_files         = $amount_inexistent_source_ini_files + $amount_inexistent_target_ini_files;
			$total_client_comparables           = get_comparables($matrix_info, $client, $required = 'count');
			$total_mandatory_ini_files          = get_mandatories($matrix_info, $client, $type_format = 'ini', $required = 'count');

			$clients_to_display[$client] [] = "<p></p><h2>" . JText::_('MOD_STOOLPIGEON_COMPARABLE_FILES_DETAILS') . " | "
				. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";

			$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_DUE_THIS_PROGRAM_ONLY') . "<b>" . $total_inexistent_ini_files . "</b>"
				. JText::_('MOD_STOOLPIGEON_FILES_WE_CAN_COMPARE') . "<b>" . $total_client_comparables . "</b>" . JText::_('MOD_STOOLPIGEON_OF') . "<b>"
				. $total_mandatory_ini_files . "</b>" . JText::_('MOD_STOOLPIGEON_FILES_READEDS_AS_MANDATORY') . "</p>";

			if ($total_client_comparables > '0')
			{
				//INCIDENCIES
				$clients_to_display[$client] [] = "<p></p><h2>" . JText::_('MOD_STOOLPIGEON_COMPARABLE_FILES_WITH_INCIDENCES') . " | "
					. JText::_('MOD_STOOLPIGEON_' . strtoupper($client)) . "</h2>";
				$clients_to_display[$client] [] = "<p>" . JText::_('MOD_STOOLPIGEON_NOW_WE_CAN_TRY_TO_CATCH') . "</p>";
				$missed_equal_source            = get_equal_not_found($matrix_info, $client, $location = 'source');
				$missed_equal_target            = get_equal_not_found($matrix_info, $client, $location = 'target');
				$missed_quotes_source           = get_quote_not_found($matrix_info, $client, $location = 'source');
				$missed_quotes_target           = get_quote_not_found($matrix_info, $client, $location = 'target');
				$q_required_source              = get_q_required($matrix_info, $client, $location = 'source');
				$q_required_target              = get_q_required($matrix_info, $client, $location = 'target');

				$keys_to_add = get_keys_non_existent_by_client($matrix_info, $client, $type_keys = 'to_add');

				$keys_to_delete = get_keys_non_existent_by_client($matrix_info, $client, $type_keys = 'to_delete');

				if ($matrix_info['mode'] == 'between_equal_language_tags')
				{
					$keys_to_revise = get_keys_text_changed($matrix_info, $client);
				}
				elseif ($matrix_info['mode'] == 'between_different_language_tags')
				{
					$keys_to_revise = get_non_translated_keys($matrix_info, $client);
				}

				$incidences = '0';

				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';

				if (!empty ($missed_equal_source) && $matrix_info['config']['display_options']['hide_source_info'] == '0')
				{
					foreach ($missed_equal_source['line'] as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE') . $files_counter
						. JText::_('MOD_STOOLPIGEON_SOURCE_' . strtoupper($client) . '_FILES_WHICH')
						. $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD_EQUAL') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}


				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';
				if (!empty ($missed_equal_target) && $matrix_info['config']['display_options']['hide_target_info'] == '0')
				{
					foreach ($missed_equal_target['line'] as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE') . $files_counter
						. JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH')
						. $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD_EQUAL') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}

				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';

				if (!empty ($missed_quotes_source) && $matrix_info['config']['display_options']['hide_source_info'] == '0')
				{
					foreach ($missed_quotes_source['line'] as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE') . $files_counter
						. JText::_('MOD_STOOLPIGEON_SOURCE_' . strtoupper($client) . '_FILES_WHICH')
						. $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD_QUOTE') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}

				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';
				if (!empty ($missed_quotes_target) && $matrix_info['config']['display_options']['hide_target_info'] == '0')
				{
					foreach ($missed_quotes_target['line'] as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE') . $files_counter
						. JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH')
						. $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD_QUOTE') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}

				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';

				if (!empty ($q_required_source) && $matrix_info['config']['display_options']['hide_source_info'] == '0')
				{
					foreach ($q_required_source['line'] as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE') . $files_counter
						. JText::_('MOD_STOOLPIGEON_SOURCE_' . strtoupper($client) . '_FILES_WHICH')
						. $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD_SYMBOL_Q') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}

				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';
				if (!empty ($q_required_target) && $matrix_info['config']['display_options']['hide_target_info'] == '0')
				{
					foreach ($q_required_target['line'] as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE') . $files_counter
						. JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH')
						. $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD_SYMBOL_Q') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}


				$files_counter = '0';
				$total_keys    = '0';
				$to_add        = '';
				if (!empty ($keys_to_add))
				{
					foreach ($keys_to_add as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_add     .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_ADD_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE')
						. $files_counter . JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH') . $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_ADD') . "</p>";
					$clients_to_display[$client] [] = $to_add;
					$incidences                     = '1';

				}

				$files_counter = '0';
				$total_keys    = '0';
				$to_delete     = '';
				if (!empty ($keys_to_delete))
				{
					foreach ($keys_to_delete as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_delete  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_DELETE_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE')
						. $files_counter . JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH') . $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_DELETE') . "</p>";
					$clients_to_display[$client] [] = $to_delete;
					$incidences                     = '1';

				}

				$files_counter = '0';
				$total_keys    = '0';
				$to_revise     = '';
				if (!empty ($keys_to_revise) && $matrix_info['mode'] == 'between_different_language_tags')
				{
					foreach ($keys_to_revise as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_TRANSLATE_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE')
						. $files_counter . JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH') . $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_TRANSLATE') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';

				}
				elseif (isset ($keys_to_revise) && $matrix_info['mode'] == 'between_equal_language_tags')
				{

					foreach ($keys_to_revise as $file => $keys)
					{
						$files_counter++;
						$total_keys = $total_keys + count($keys);
						$to_revise  .= "<b>" . $target_language_tag . "." . $file . "</b> (<b>" . count($keys) . "</b>"
							. JText::_('MOD_STOOLPIGEON_TO_REVISE_L') . "<br />";
						unset($file, $keys);
					}
					$clients_to_display[$client] [] = "<p class='alert'>" . JText::_('MOD_STOOLPIGEON_THERE_ARE')
						. $files_counter . JText::_('MOD_STOOLPIGEON_TARGET_' . strtoupper($client) . '_FILES_WHICH') . $total_keys
						. JText::_('MOD_STOOLPIGEON_KEYS_TO_REVISE') . "</p>";
					$clients_to_display[$client] [] = $to_revise;
					$incidences                     = '1';
				}

				if ($matrix_info['config']['display_options']['hide_tables'] == '0' && $incidences == '1')
				{
					$clients_to_display[$client] [] = "<br /><h2>" . JText::_('MOD_STOOLPIGEON_LETS_GO_ROCK_N_ROLL') . "</h2>";
					$clients_to_display[$client] [] = "<table width='100%'><tr><td class='type4' colspan='14'>"
						. JText::_('MOD_STOOLPIGEON_' . strtoupper($client) . '_ZONE') . "</td></tr>";

					if (!empty($missed_equal_source['line']) && $matrix_info['config']['display_options']['hide_source_info'] == '0')
					{
						foreach ($missed_equal_source['line'] as $file => $lines)
						{
							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. JText::_('MOD_STOOLPIGEON_POSSIBLE_PARSING_ERROR_SOURCE')
								. $source_language_tag . "." . $file . "</td></tr>";

							$altern_style = '0';

							foreach ($lines as $line)
							{
								$source_line = $line;
								$catched     = $missed_equal_source['text'][$file][$line];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $catched = htmlspecialchars ($catched) : $catched;
								$catched = htmlspecialchars($catched);

								$clients_to_display[$client] [] = "<tr><td class='type_1' colspan='14'>"
									. JText::_('MOD_STOOLPIGEON_SYMBOL_EQUAL_NOT_FOUND')
									. $line . "</td></tr>";


								if ($matrix_info['config']['display_options']['display_catched'] == '1')
								{
									$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
										. JText::_('MOD_STOOLPIGEON_CATCHED') . $catched . "</td></tr>";
								}
								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($line);
							}

							unset($file, $lines);

						}

					}//end isset

					if (!empty($missed_equal_target['line']) && $matrix_info['config']['display_options']['hide_target_info'] == '0')
					{
						foreach ($missed_equal_target['line'] as $file => $lines)
						{
							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. JText::_('MOD_STOOLPIGEON_POSSIBLE_PARSING_ERROR_TARGET')
								. $target_language_tag . "." . $file . "</td></tr>";

							$altern_style = '0';

							foreach ($lines as $line)
							{
								$source_line = $line;
								$catched     = $missed_equal_target['text'][$file][$line];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $catched = htmlspecialchars ($catched) : $catched;
								$catched = htmlspecialchars($catched);

								$clients_to_display[$client] [] = "<tr><td class='type_1' colspan='14'>"
									. JText::_('MOD_STOOLPIGEON_SYMBOL_EQUAL_NOT_FOUND')
									. $line . "</td></tr>";


								if ($matrix_info['config']['display_options']['display_catched'] == '1')
								{
									$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
										. JText::_('MOD_STOOLPIGEON_CATCHED') . $catched . "</td></tr>";
								}
								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($line);
							}

							unset($file, $lines);

						}

					}//end isset


					if (!empty($missed_quotes_source['line']) && $matrix_info['config']['display_options']['hide_source_info'] == '0')
					{
						foreach ($missed_quotes_source['line'] as $file => $lines)
						{
							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. JText::_('MOD_STOOLPIGEON_POSSIBLE_PARSING_ERROR_SOURCE')
								. $source_language_tag . "." . $file . "</td></tr>";

							$altern_style = '0';

							foreach ($lines as $line)
							{
								$source_line = $line;
								$catched     = $missed_quotes_source['text'][$file][$line];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? htmlspecialchars ($catched) : $catched;
								$catched = htmlspecialchars($catched);

								$clients_to_display[$client] [] = "<tr><td class='type_1' colspan='14'>"
									. JText::_('MOD_STOOLPIGEON_SYMBOL_QUOTE_NOT_FOUND')
									. $line . "</td></tr>";


								if ($matrix_info['config']['display_options']['display_catched'] == '1')
								{
									$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
										. JText::_('MOD_STOOLPIGEON_CATCHED') . $catched . "</td></tr>";
								}
								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($line);
							}

							unset($file, $lines);

						}

					}//end isset

					if (!empty($missed_quotes_target['line']) && $matrix_info['config']['display_options']['hide_target_info'] == '0')
					{
						foreach ($missed_quotes_target['line'] as $file => $lines)
						{
							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. JText::_('MOD_STOOLPIGEON_POSSIBLE_PARSING_ERROR_TARGET')
								. $target_language_tag . "." . $file . "</td></tr>";

							$altern_style = '0';

							foreach ($lines as $line)
							{
								$source_line = $line;
								$catched     = $missed_quotes_target['text'][$file][$line];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $catched = htmlspecialchars ($catched) : $catched;
								$catched = htmlspecialchars($catched);

								$clients_to_display[$client] [] = "<tr><td class='type_1' colspan='14'>"
									. JText::_('MOD_STOOLPIGEON_SYMBOL_QUOTE_NOT_FOUND')
									. $line . "</td></tr>";


								if ($matrix_info['config']['display_options']['display_catched'] == '1')
								{
									$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
										. JText::_('MOD_STOOLPIGEON_CATCHED') . $catched . "</td></tr>";
								}
								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($line);
							}

							unset($file, $lines);

						}

					}//end isset


					if (!empty($q_required_source['line']) && $matrix_info['config']['display_options']['hide_source_info'] == '0')
					{
						foreach ($q_required_source['line'] as $file => $lines)
						{
							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. JText::_('MOD_STOOLPIGEON_POSSIBLE_PARSING_ERROR_SOURCE')
								. $source_language_tag . "." . $file . "</td></tr>";

							$altern_style = '0';

							foreach ($lines as $line)
							{
								$source_line = $line;
								$catched     = $q_required_source['text'][$file][$line];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $catched = htmlspecialchars ($catched) : $catched;
								$catched = htmlspecialchars($catched);

								$clients_to_display[$client] [] = "<tr><td class='type_1' colspan='14'>"
									. JText::_('MOD_STOOLPIGEON_SYMBOL_Q_NOT_FOUND')
									. $line . "</td></tr>";


								if ($matrix_info['config']['display_options']['display_catched'] == '1')
								{
									$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
										. JText::_('MOD_STOOLPIGEON_CATCHED') . $catched . "</td></tr>";
								}
								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($line);
							}

							unset($file, $lines);

						}

					}//end isset

					if (!empty($q_required_target['line']) && $matrix_info['config']['display_options']['hide_target_info'] == '0')
					{
						foreach ($q_required_target['line'] as $file => $lines)
						{
							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. JText::_('MOD_STOOLPIGEON_POSSIBLE_PARSING_ERROR_TARGET')
								. $target_language_tag . "." . $file . "</td></tr>";

							$altern_style = '0';

							foreach ($lines as $line)
							{
								$source_line = $line;
								$catched     = $q_required_target['text'][$file][$line];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $catched = htmlspecialchars ($catched) : $catched;
								$catched = htmlspecialchars($catched);

								$clients_to_display[$client] [] = "<tr><td class='type_1' colspan='14'>"
									. JText::_('MOD_STOOLPIGEON_SYMBOL_Q_NOT_FOUND')
									. $line . "</td></tr>";


								if ($matrix_info['config']['display_options']['display_catched'] == '1')
								{
									$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
										. JText::_('MOD_STOOLPIGEON_CATCHED') . $catched . "</td></tr>";
								}
								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($line);
							}

							unset($file, $lines);

						}

					}//end isset


					if (!empty($keys_to_add))
					{
						foreach ($keys_to_add as $file => $keys)
						{

							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. $source_language_tag . "." . $file
								. JText::_('MOD_STOOLPIGEON_VERSION_B')
								. $source_version . JText::_('MOD_STOOLPIGEON_VERSION_E')
								. JText::_('MOD_STOOLPIGEON_VS') . $target_language_tag . "." . $file
								. JText::_('MOD_STOOLPIGEON_VERSION_B') . $target_version
								. JText::_('MOD_STOOLPIGEON_VERSION_E') . "</td></tr>";

							$altern_style = '0';

							$clients_to_display[$client] [] = "<tr><td class='type1'colspan='4'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . "</td><td class='type1'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_LINE') . "</td><td class='type3' colspan='4'>"
								. JText::_('MOD_STOOLPIGEON_KEY') . "</td><td class='type1'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_LINE') . "</td><td class='type1' colspan='4'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . "</td></tr>";


							foreach ($keys as $key)
							{


								$source_line = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
								[$file]['counter']['lines'][$key];

								$source_text = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
								[$file]['keys'][$key];


								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $source_text = htmlspecialchars ($source_text) : $source_text;
								$source_text = htmlspecialchars($source_text);
								$key         = htmlspecialchars($key);


								$header_lines_source = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
								[$file]['counter']['first_key'];

								$header_lines_target = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
								[$file]['counter']['first_key'];

								$lines_diff = ($header_lines_target - $header_lines_source) + $source_line;

								($matrix_info['config']['experimental_options']['relative_target_line'] == '1')
									? $target_line = $lines_diff : $target_line = '---';

								$clients_to_display[$client] [] = "<tr><td class='to_add'colspan='4'>" . $source_text . "</td><td class='number'>" . $source_line
									. "</td><td class='altern_" . $altern_style . "' colspan='4'>$key</td><td class='number'>" . $target_line
									. "</td><td class='to_add' colspan='4'>" . JText::_('MOD_STOOLPIGEON_TO_ADD_IN_TARGET') . "</td></tr>";


								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($key);
							}

							unset($file, $keys);
						}
					} //end isset


					if (!empty($keys_to_delete))
					{
						foreach ($keys_to_delete as $file => $keys)
						{

							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. $source_language_tag . "." . $file
								. JText::_('MOD_STOOLPIGEON_VERSION_B')
								. $source_version . JText::_('MOD_STOOLPIGEON_VERSION_E')
								. JText::_('MOD_STOOLPIGEON_VS') . $target_language_tag . "." . $file
								. JText::_('MOD_STOOLPIGEON_VERSION_B') . $target_version
								. JText::_('MOD_STOOLPIGEON_VERSION_E') . "</td></tr>";

							$altern_style = '0';

							$clients_to_display[$client] [] = "<tr><td class='type1' colspan='4'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . "</td><td class='type1'>"
								. JText::_('MOD_STOOLPIGEON_SOURCE_LINE') . "</td><td class='type3' colspan='4'>"
								. JText::_('MOD_STOOLPIGEON_KEY') . "</td><td class='type1'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_LINE') . "</td><td class='type1' colspan='4'>"
								. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . "</td></tr>";

							foreach ($keys as $key)
							{


								$target_line = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
								[$file]['counter']['lines'][$key];

								$target_text = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
								[$file]['keys'][$key];


								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $target_text = htmlspecialchars ($target_text) : $target_text;
								$target_text = htmlspecialchars($target_text);

								$key = htmlspecialchars($key);


								$clients_to_display[$client] [] = "<tr><td class='to_delete' colspan='4'>"
									. JText::_('MOD_STOOLPIGEON_TO_DELETE_IN_TARGET')
									. "</td><td class='number'>---</td><td class='altern_" . $altern_style . "' colspan='4'>" . $key . "</td><td class='number'>"
									. $target_line . "</td><td class='to_delete' colspan='4'>" . $target_text . "</td></tr>";


								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($key);
							}

							unset($file, $keys);
						}
					} //end isset

					if (!empty($keys_to_revise))
					{

						foreach ($keys_to_revise as $file => $keys)
						{
							$altern_style = '0';

							$clients_to_display[$client] [] = "<tr><td class='type3' colspan='14'>"
								. $source_language_tag . "." . $file
								. JText::_('MOD_STOOLPIGEON_VERSION_B') . $source_version
								. JText::_('MOD_STOOLPIGEON_VERSION_E')
								. JText::_('MOD_STOOLPIGEON_VS') . $target_language_tag . "." . $file
								. JText::_('MOD_STOOLPIGEON_VERSION_B') . $target_version
								. JText::_('MOD_STOOLPIGEON_VERSION_E') . "</td></tr>";

							foreach ($keys as $key)
							{

								$source_line = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
								[$file]['counter']['lines'][$key];

								$source_text = $matrix_info['file_existent'][$client . '_ini_files']['source']['files_info']
								[$file]['keys'][$key];

								$target_line = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
								[$file]['counter']['lines'][$key];

								$target_text = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info']
								[$file]['keys'][$key];

								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $source_text = htmlspecialchars ($source_text) : $source_text;
								$source_text = htmlspecialchars($source_text);
								//($matrix_info['config']['experimental_extras']['scape_html'] == '1')
								//? $target_text = htmlspecialchars ($target_text) : $target_text;
								$target_text = htmlspecialchars($target_text);

								$key = htmlspecialchars($key);
								if ($matrix_info['mode'] == 'between_equal_language_tags')
								{
									$clients_to_display[$client] [] = "<tr><td class='altern_$altern_style' rowspan='4' colspan='4'>"
										. $key . "</td><td class='source_text' colspan='10'>"
										. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . " <b>Line:</b> " . $source_line
										. "</td></tr><tr><td colspan='10'>"
										. $source_text . "</td></tr><tr><td class='target_text' colspan='10'>"
										. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . " <b>Line:</b> " . $target_line
										. "</td></tr><tr><td colspan='10'>"
										. $target_text . "</td></tr>";

									if ($matrix_info['config']['display_options']['display_diff'] == '1')
									{
										$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
											. htmlDiff($target_text, $source_text) . "</td></tr>";
									}

								}
								elseif ($matrix_info['mode'] == 'between_different_language_tags')
								{

									$clients_to_display[$client] [] = "<tr><td class='altern_$altern_style' rowspan='4' colspan='4'>"
										. $key . "</td><td class='source_text' colspan='10'>"
										. JText::_('MOD_STOOLPIGEON_SOURCE_TEXT') . " <b>"
										. JText::_('MOD_STOOLPIGEON_LINE_2') . "</b> " . $source_line
										. "</td></tr><tr><td colspan='10'>"
										. $source_text . "</td></tr><tr><td class='target_text' colspan='10'>"
										. JText::_('MOD_STOOLPIGEON_TARGET_TEXT') . " <b>"
										. JText::_('MOD_STOOLPIGEON_LINE_2') . "</b> " . $target_line
										. "</td></tr><tr><td colspan='10'>"
										. $target_text . "</td></tr>";

									if ($matrix_info['config']['display_options']['display_diff'] == '1')
									{
										$clients_to_display[$client] [] = "<tr><td class='diff' colspan='14'>"
											. htmlDiff($target_text, $source_text) . "</td></tr>";
									}

								}

								if ($altern_style == '0')
								{
									$altern_style = '1';
								}
								else
								{
									$altern_style = '0';
								}
								unset($key);
							}

							unset($file, $keys);
						}

					}//end isset


					$clients_to_display[$client] [] = "</table>";

				}
				elseif ($matrix_info['config']['display_options']['hide_tables'] == '0' && $incidences == '0')
				{
					$clients_to_display[$client] [] = "<br /><h2>" . JText::_('MOD_STOOLPIGEON_WOW_YOU_ARE_CRACK') . "</h2>";
				}


			}
			elseif ($total_client_comparables <= '0')
			{

				$clients_to_display[$client] [] = "<br /><h2>" . JText::_('MOD_STOOLPIGEON_LOL_NO_ONE') . "</h2>";

			}//end client comparables

		}//end if client is selected
		unset($client);
	}//end for each client
}

function get_mandatories(&$matrix_info = array(), $client = '', $type_format = '', $required = '')
{
	if ($type_format == 'all')
	{
		if ($required == 'content')
		{
			if (isset($matrix_info['mandatories'][$client]))
			{
				return $matrix_info['mandatories'][$client];
			}
			else
			{
				return array();
			}

		}
		elseif ($required == 'count')
		{

			if (isset($matrix_info['mandatories'][$client]))
			{
				return count($matrix_info['mandatories'][$client]);
			}
			else
			{
				return 0;
			}
		}
	}

	if ($type_format == 'ini')
	{
		if ($required == 'content')
		{

			if (isset($matrix_info['mandatories'][$client . '_ini_files']))
			{
				return $matrix_info['mandatories'][$client . '_ini_files'];
			}
			else
			{
				return array();
			}

		}
		elseif ($required == 'count')
		{

			if (isset($matrix_info['mandatories'][$client . '_ini_files']))
			{
				return count($matrix_info['mandatories'][$client . '_ini_files']);
			}
			else
			{
				return 0;
			}
		}
	}

	if ($type_format == 'non_ini')
	{
		if ($required == 'content')
		{

			if (isset($matrix_info['mandatories'][$client . '_non_ini_files']))
			{
				return $matrix_info['mandatories'][$client . '_non_ini_files'];
			}
			else
			{
				return array();
			}

		}
		elseif ($required == 'count')
		{

			if (isset($matrix_info['mandatories'][$client . '_non_ini_files']))
			{
				return count($matrix_info['mandatories'][$client . '_non_ini_files']);
			}
			else
			{
				return 0;
			}
		}
	}

	if ($type_format == 'non_ini_tagged')
	{
		if ($required == 'content')
		{

			if (isset($matrix_info['mandatories'][$client . '_non_ini_tagged_files']))
			{
				return $matrix_info['mandatories'][$client . '_non_ini_tagged_files'];
			}
			else
			{
				return array();
			}

		}
		elseif ($required == 'count')
		{

			if (isset($matrix_info['mandatories'][$client . '_non_ini_tagged_files']))
			{
				return count($matrix_info['mandatories'][$client . '_non_ini_tagged_files']);
			}
			else
			{
				return 0;
			}
		}
	}
}

function get_files_existent(&$matrix_info = array(), $client = '', $type_format = '', $location = '', $required = '')
{

	if ($type_format === 'ini')
	{

		if (isset($matrix_info['file_existent'][$client . '_ini_files'][$location]['files']))
		{
			if ($required == 'content')
			{
				return $matrix_info['file_existent'][$client . '_ini_files'][$location]['files'];
			}
			elseif ($required == 'count')
			{
				return count($matrix_info['file_existent'][$client . '_ini_files'][$location]['files']);
			}

		}
		elseif (!isset($matrix_info['file_existent'][$client . '_ini_files'][$location]['files']))
		{

			if ($required == 'content')
			{
				return array();
			}
			elseif ($required == 'count')
			{
				return '0';
			}

		}
	}
	elseif ($type_format === 'non_ini')
	{

		if (isset($matrix_info['file_existent'][$client . '_non_ini_files'][$location]['files']))
		{
			if ($required == 'content')
			{
				return $matrix_info['file_existent'][$client . '_non_ini_files'][$location]['files'];
			}
			elseif ($required == 'count')
			{
				return count($matrix_info['file_existent'][$client . '_non_ini_files'][$location]['files']);
			}

		}
		elseif (!isset($matrix_info['file_existent'][$client . '_non_ini_files'][$location]['files']))
		{

			if ($required == 'content')
			{
				return array();
			}
			elseif ($required == 'count')
			{
				return '0';
			}

		}
	}
	elseif ($type_format === 'non_ini_tagged')
	{

		if (isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files'][$location]['files']))
		{
			if ($required == 'content')
			{
				return $matrix_info['file_existent'][$client . '_non_ini_tagged_files'][$location]['files'];
			}
			elseif ($required == 'count')
			{
				return count($matrix_info['file_existent'][$client . '_non_ini_tagged_files'][$location]['files']);
			}

		}
		elseif (!isset($matrix_info['file_existent'][$client . '_non_ini_tagged_files'][$location]['files']))
		{

			if ($required == 'content')
			{
				return array();
			}
			elseif ($required == 'count')
			{
				return '0';
			}

		}
	}

}


function get_files_inexistent(&$matrix_info = array(), $client = '', $type_format = '', $location = '', $required = '')
{

	if ($type_format == 'ini')
	{

		if (isset($matrix_info['incidences']['file_non_existent'][$client . '_ini_files'][$location]))
		{
			if ($required == 'content')
			{
				return $matrix_info['incidences']['file_non_existent'][$client . '_ini_files'][$location];
			}
			elseif ($required == 'count')
			{
				return count($matrix_info['incidences']['file_non_existent'][$client . '_ini_files'][$location]);
			}

		}
		elseif (!isset($matrix_info['incidences']['file_non_existent'][$client . '_ini_files'][$location]))
		{

			if ($required == 'content')
			{
				return array();
			}
			elseif ($required == 'count')
			{
				return '0';
			}

		}
	}

	if ($type_format == 'non_ini')
	{

		if (isset($matrix_info['incidences']['file_non_existent'][$client . '_non_ini_files'][$location]))
		{
			if ($required == 'content')
			{
				return $matrix_info['incidences']['file_non_existent'][$client . '_non_ini_files'][$location];
			}
			elseif ($required == 'count')
			{
				return count($matrix_info['incidences']['file_non_existent'][$client . '_non_ini_files'][$location]);
			}

		}
		elseif (!isset($matrix_info['incidences']['file_non_existent'][$client . '_non_ini_files'][$location]))
		{

			if ($required == 'content')
			{
				return array();
			}
			elseif ($required == 'count')
			{
				return '0';
			}

		}
	}

	if ($type_format == 'non_ini_tagged')
	{

		if (isset($matrix_info['incidences']['file_non_existent'][$client . '_non_ini_tagged_files'][$location]))
		{
			if ($required == 'content')
			{
				return $matrix_info['incidences']['file_non_existent'][$client . '_non_ini_tagged_files'][$location];
			}
			elseif ($required == 'count')
			{
				return count($matrix_info['incidences']['file_non_existent'][$client . '_non_ini_tagged_files'][$location]);
			}

		}
		elseif (!isset($matrix_info['incidences']['file_non_existent'][$client . '_non_ini_tagged_files'][$location]))
		{

			if ($required == 'content')
			{
				return array();
			}
			elseif ($required == 'count')
			{
				return '0';
			}

		}
	}

}

function get_comparables(&$matrix_info = array(), $client = '', $required = '')
{
	if (isset($matrix_info[$client . '_comparables']))
	{
		if ($required == 'content')
		{
			return $matrix_info[$client . '_comparables'];
		}
		elseif ($required == 'count')
		{
			return count($matrix_info[$client . '_comparables']);
		}
	}
	elseif (!isset($matrix_info[$client . '_comparables']))
	{
		if ($required == 'content')
		{
			return array();
		}
		elseif ($required == 'count')
		{
			return '0';
		}
	}
}

function get_keys_non_existent_by_client(&$matrix_info = array(), $client = '', $type_keys = '')
{
	if ($type_keys == 'to_add')
	{


		if (isset($matrix_info['incidences']['key_non_existent_in_target'][$client]))
		{

			return $matrix_info['incidences']['key_non_existent_in_target'][$client];

		}
		elseif (!isset($matrix_info['incidences']['key_non_existent_in_target'][$client]))
		{

			return array();

		}

	}
	elseif ($type_keys == 'to_delete')
	{

		if (isset($matrix_info['incidences']['key_non_existent_in_source'][$client]))
		{

			return $matrix_info['incidences']['key_non_existent_in_source'][$client];

		}
		elseif (!isset($matrix_info['incidences']['key_non_existent_in_source'][$client]))
		{

			return array();

		}

	}
}

function get_keys_non_existent_by_file(&$matrix_info = array(), $client = '', $file = '', $type_keys = '', $required = '')
{
	if ($type_keys == 'to_add')
	{

		if (isset($matrix_info['incidences']['key_non_existent_in_target'][$client][$file]))
		{

			if ($required == 'content')
			{

				return $matrix_info['incidences']['key_non_existent_in_target'][$client][$file];

			}
			elseif ($required == 'count')
			{

				return count($matrix_info['incidences']['key_non_existent_in_target'][$client][$file]);

			}


		}
		elseif (!isset($matrix_info['incidences']['key_non_existent_in_target'][$client][$file]))
		{

			if ($required == 'content')
			{

				return array();

			}
			elseif ($required == 'count')
			{

				return '0';

			}

		}

	}
	elseif ($type_keys == 'to_delete')
	{

		if (isset($matrix_info['incidences']['key_non_existent_in_source'][$client][$file]))
		{

			if ($required == 'content')
			{

				return $matrix_info['incidences']['key_non_existent_in_source'][$client][$file];

			}
			elseif ($required == 'count')
			{

				return count($matrix_info['incidences']['key_non_existent_in_source'][$client][$file]);

			}


		}
		elseif (!isset($matrix_info['incidences']['key_non_existent_in_source'][$client][$file]))
		{

			if ($required == 'content')
			{
				return array();

			}
			elseif ($required == 'count')
			{

				return '0';

			}

		}

	}
}

function get_keys_text_changed(&$matrix_info = array(), $client = '')
{
	if (isset($matrix_info['incidences']['between_equal_language_tags'][$client]['keys_text_changed']))
	{

		return $matrix_info['incidences']['between_equal_language_tags'][$client]['keys_text_changed'];

	}
	elseif (!isset($matrix_info['incidences']['between_equal_language_tags'][$client]['keys_text_changed']))
	{

		return array();

	}
}

function get_non_translated_keys(&$matrix_info = array(), $client = '')
{
	if (isset($matrix_info['incidences']['between_different_language_tags'][$client]['non_translated']))
	{

		return $matrix_info['incidences']['between_different_language_tags'][$client]['non_translated'];

	}
	elseif (!isset($matrix_info['incidences']['between_different_language_tags'][$client]['non_translated']))
	{

		return array();

	}
}

function get_quote_not_found(&$matrix_info = array(), $client = '', $location = '')
{
	if (isset($matrix_info['incidences']['quote_not_found'][$location][$client]))
	{

		return $matrix_info['incidences']['quote_not_found'][$location][$client];

	}
	elseif (!isset($matrix_info['incidences']['quote_not_found'][$location][$client]))
	{

		return array();

	}
}

function get_q_required(&$matrix_info = array(), $client = '', $location = '')
{
	if (isset($matrix_info['incidences']['q_required'][$location][$client]))
	{

		return $matrix_info['incidences']['q_required'][$location][$client];

	}
	elseif (!isset($matrix_info['incidences']['q_required'][$location][$client]))
	{

		return array();

	}
}

function get_equal_not_found(&$matrix_info = array(), $client = '', $location = '')
{
	if (isset($matrix_info['incidences']['equal_not_found'][$location][$client]))
	{

		return $matrix_info['incidences']['equal_not_found'][$location][$client];

	}
	elseif (!isset($matrix_info['incidences']['equal_not_found'][$location][$client]))
	{

		return array();

	}
}

function catch_cookies(&$matrix_info = array())
{
	if ($matrix_info['config']['experimental_mode']['enable_edit_mode'] == '1')
	{
		$clients_availables = array('admin', 'site', 'installation');

		foreach ($clients_availables as $client_available)
		{
			if (isset($matrix_info['config']['client_selection'][$client_available]))
			{
				$clients[] = $client_available;
			}
		}

		$actual_module                 = $matrix_info['actual_module'];
		$module_id                     = $matrix_info['module_id'];
		$stored_cookies                = $_COOKIE;
		$matrix_info['stored_cookies'] = $stored_cookies;
		$source_language_tag           = $matrix_info['config']['source_language_tag'];
		$target_language_tag           = $matrix_info['config']['target_language_tag'];
		$source_version                = $matrix_info['config']['version_options']['source_version'];
		$coordinated_task              = $matrix_info['config']['experimental_options']['coordinated_task'];
		$joomla_path                   = JURI::base(true);
		$document                      = JFactory::getDocument();
		$document->addScript('media/mod_stoolpigeon/js/edit_text.js');
		$document->addScript('media/mod_stoolpigeon/js/alerts.js');


		if (!isset($_COOKIE['changes_diff_tags']))
		{
			$there_are_changes_diff_tags = '2';
			setcookie('changes_diff_tags', '2');
		}
		elseif ($_COOKIE['changes_diff_tags'] == '1')
		{
			$there_are_changes_diff_tags = '1';
		}
		elseif ($_COOKIE['changes_diff_tags'] == '0')
		{
			$there_are_changes_diff_tags = '0';
		}
		elseif ($_COOKIE['changes_diff_tags'] == '2')
		{
			$there_are_changes_diff_tags = '2';
		}

		if (!isset($_COOKIE['confirm_pack']))
		{
			$there_are_confirm_pack = '2';
			setcookie('confirm_pack', '2');
		}
		elseif ($_COOKIE['confirm_pack'] == '1')
		{
			$there_are_confirm_pack = '1';
		}
		elseif ($_COOKIE['confirm_pack'] == '0')
		{
			$there_are_confirm_pack = '0';
		}
		else
		{
			$there_are_confirm_pack = '2';
		}

		if (!isset($_COOKIE['confirm_discart_diff_tags']))
		{
			$there_are_confirm_discart_diff_tags = '2';
			setcookie('confirm_discart_diff_tags', '2');
		}
		elseif ($_COOKIE['confirm_discart_diff_tags'] == '1')
		{
			$there_are_confirm_discart_diff_tags = '1';
		}
		elseif ($_COOKIE['confirm_discart_diff_tags'] == '0')
		{
			$there_are_confirm_discart_diff_tags = '0';
		}
		elseif ($_COOKIE['confirm_discart_diff_tags'] == '2')
		{
			$there_are_confirm_discart_diff_tags = '2';
		}


		if (!isset($_COOKIE['confirm_discart_coordinated_task']))
		{
			$there_are_confirm_discart_coordinated_task = '2';
			setcookie('confirm_discart_coordinated_task', '2');
		}
		elseif ($_COOKIE['confirm_discart_coordinated_task'] == '1')
		{
			$there_are_confirm_discart_coordinated_task = '1';
		}
		elseif ($_COOKIE['confirm_discart_coordinated_task'] == '0')
		{
			$there_are_confirm_discart_coordinated_task = '0';
		}
		elseif ($_COOKIE['confirm_discart_coordinated_task'] == '2')
		{
			$there_are_confirm_discart_coordinated_task = '2';
		}


		if ($there_are_changes_diff_tags == '1' && $there_are_confirm_pack == '1')
		{

			$done = '0';
			foreach ($clients as $client)
			{
				if ($matrix_info['config']['client_selection'][$client] == '1')
				{
					$client_data                    = array();
					$client_data['stored_cookies']  = $stored_cookies;
					$client_data['type']            = '_edited_files_between_';
					$client_data['discart_changes'] = '0';
					$client_data['new_base_path']   = JPATH_ROOT . "/" . "tmp" . "/" . "mod_stoolpigeon_edited_files";
					$client_data['files_to_zip']    = '';


					diff_tags_set_changes($matrix_info, $client, $client_data);
					$done = '1';

					JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_PACKAGE_CREATED'));

				}
				unset($client);
			}


			if ($done == '1')
			{
				setcookie('confirm_pack', '2');
				setcookie('confirm_discart_diff_tags', '2');
			}


		}
		elseif ($there_are_changes_diff_tags == '1' && $there_are_confirm_discart_diff_tags == '1')
		{

			diff_tags_delete_stored_cookies($stored_cookies, $actual_module);
			setcookie('confirm_pack', '2');
			setcookie('confirm_discart_diff_tags', '2');
			setcookie('changes_diff_tags', '2');
			setcookie('confirm_discart_coordinated_task', '2');
			setcookie('changes_coordinated_task[' . $source_language_tag . '][' . $source_version . ']', '2');


		}
		elseif ($there_are_confirm_discart_coordinated_task == '1' && $coordinated_task == '1')
		{

			coordinated_task_delete_stored_cookies($stored_cookies, $source_language_tag, $source_version,
				$target_language_tag, $actual_module);

			setcookie('confirm_pack', '2');
			setcookie('confirm_discart_diff_tags', '2');
			//setcookie('changes_diff_tags', '2');
			setcookie('confirm_discart_coordinated_task', '2');
		}

	}

}

function backup_target_files(&$matrix_info = array(), $client = '', &$client_data = array())
{
	$target_files['ini']            = get_files_existent($matrix_info, $client, $type_format = 'ini', $location = 'target', $required = 'content');
	$target_files['non_ini']        = get_files_existent($matrix_info, $client, $type_format = 'non_ini', $location = 'target', $required = 'content');
	$target_files['non_ini_tagged'] = get_files_existent($matrix_info, $client, $type_format = 'non_ini_tagged', $location = 'target', $required = 'content');


	$state = '';

	$tlt = $matrix_info['config']['target_language_tag'];

	$tv = $matrix_info['config']['version_options']['target_version'];

	if ($client == 'admin')
	{
		$target_path = $matrix_info['config']['paths']['taf'];

	}
	elseif ($client == 'site')
	{

		$target_path = $matrix_info['config']['paths']['tsf'];

	}
	elseif ($client == 'installation')
	{

		$target_path = $matrix_info['config']['paths']['tif'];
	}

	//$display_package_link = $matrix_info['config']['experimental_options']['display_package_link'];
	$new_base_path = $client_data['new_base_path'];

	//$info = array();

	$files_to_zip = '';

	if (!JFolder::create($new_base_path))
	{
	}
	if (!JFolder::create($new_base_path . '/' . $client))
	{
	}

	//$format_types = array('ini', 'non_ini');
	$format_types = array('ini', 'non_ini', 'non_ini_tagged');

	foreach ($format_types as $format_type)
	{

		if (!empty($matrix_info['incidences']['file_non_existent'][$client . '_' . $format_type . '_files']['target']))
		{
			$missed_target_files[$client] = $matrix_info['incidences'][$client . '_' . $format_type . '_files']['target'];


			foreach ($missed_target_files[$client] as $file)
			{
				if ($format_type == 'non_ini')
				{
					$target_file = $target_path . '/' . $file;
				}
				else
				{
					$target_file = $target_path . '/' . $tlt . '.' . $file;
				}

				if ($matrix_info['config']['experimental_options']['report_files_keys_addeds_or_deleteds'] == '1')
				{
					echo "<br />" . JText::_('MOD_STOOLPIGEON_WIP') . "<b>" . $target_file . "</b>"
						. JText::_('MOD_STOOLPIGEON_WIP_IS_NOT_PRESENT_T');
				}
				unset($file);
			}
		}


		if (!empty($target_files[$format_type]))
		{
			foreach ($target_files[$format_type] as $file)
			{

				if ($format_type == 'non_ini')
				{
					$target_file = $file;

				}
				else
				{
					$target_file = $tlt . '.' . $file;

				}

				$new_target_file_path = $new_base_path . '/' . $client . '/' . $target_file;
				JFile::copy($target_path . '/' . $target_file, $new_target_file_path);

				if ($matrix_info['config']['experimental_options']['report_files_keys_addeds_or_deleteds'] == '1')
				{
					echo "<br /><font color='green'>" . JText::_('MOD_STOOLPIGEON_COPIED')
						. "</font> | " . strtoupper($client) . " | <font color='green'>" . JText::_('MOD_STOOLPIGEON_TARGET_FILE')
						. "</font>" . $target_file . "<br />";
				}

				$files_to_zip .= $new_target_file_path . ",";
				unset($file);
			}

		}
		else
		{

			echo "<br />" . JText::_('MOD_STOOLPIGEON_WTF') . "<b>" . $format_type . "</b>"
				. JText::_('MOD_STOOLPIGEON_WTF_ARE_NOT_PRESENT');

		}
		unset($format_type);
	}

	if ($files_to_zip != '')
	{
		$client_data['files_to_zip'] = $files_to_zip;
		create_files_pack($matrix_info, $client, $client_data);
	}
	else
	{
		JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_WITHOUT_FILES_TO_SEND') . $client);
	}

}

function sort_keys(&$matrix_info = array(), $client = '', &$client_data = array())
{
	$state             = '';
	$files_comparables = get_comparables($matrix_info, $client, $required = 'content');
	$slt               = $matrix_info['config']['source_language_tag'];
	$tlt               = $matrix_info['config']['target_language_tag'];
	$sv                = $matrix_info['config']['version_options']['source_version'];
	$tv                = $matrix_info['config']['version_options']['target_version'];

	if ($client == 'admin')
	{
		$source_path = $matrix_info['config']['paths']['saf'];
		$target_path = $matrix_info['config']['paths']['taf'];

	}
	elseif ($client == 'site')
	{

		$source_path = $matrix_info['config']['paths']['ssf'];
		$target_path = $matrix_info['config']['paths']['tsf'];

	}
	elseif ($client == 'installation')
	{

		$source_path = $matrix_info['config']['paths']['sif'];
		$target_path = $matrix_info['config']['paths']['tif'];
	}

	if (isset($matrix_info['incidences']['keys_to_keep_in_target'][$client]))
	{
		$files_with_keys_to_keep_in_target = $matrix_info['incidences']['keys_to_keep_in_target'][$client];
	}
	else
	{
		$files_with_keys_to_keep_in_target = array();
	}

	$display_package_link = $matrix_info['config']['experimental_options']['display_package_link'];
	$new_base_path        = $client_data['new_base_path'];

	if (!JFolder::create($new_base_path))
	{
	}
	if (!JFolder::create($new_base_path . '/' . $client))
	{
	}

	if (!empty($files_comparables))
	{
		$files_to_zip = "";
		foreach ($files_comparables as $file_comparable)
		{
			$without_sections_disturbing = '';

			if (isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable])
				&& isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
			{
				//echo "HAY EN LOS DOS LADOS<br />";
				$sections_to_delete = array_diff($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable],
					$matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable]);

				$sections_to_add = array_diff($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable],
					$matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]);

				if (empty($sections_to_add) && empty($sections_to_delete))
				{

					$without_sections_disturbing = '1';
				}
				else
				{
					//echo "TO ADD";
					//print_r($sections_to_add);
					//echo "TO DELETE";
					//print_r($sections_to_delete);
					$without_sections_disturbing = '0';
				}


			}
			elseif (!isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable])
				&& !isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
			{
				//echo "NO HAY EN NINGUNO DE LOS DOS LADOS<br />";
				$without_sections_disturbing = '1';
			}
			elseif (!isset($matrix_info['incidences']['with_section_present']['source'][$client]['text'][$file_comparable])
				|| !isset($matrix_info['incidences']['with_section_present']['target'][$client]['text'][$file_comparable]))
			{
				//echo "HAY SOLO EN UNO DE LOS DOS LADOS<br />";
				$without_sections_disturbing = '0';
			}

			if (isset($matrix_info['incidences']['key_non_existent_in_target'][$client][$file_comparable])
				|| isset($matrix_info['incidences']['key_non_existent_in_source'][$client][$file_comparable])
				|| $without_sections_disturbing == '0')
			{
				//with keys pending to add or delete this one can not work correctlly
				echo "<br /><font color='red'>" . JText::_('MOD_STOOLPIGEON_WARNING_INCOMPLETE_TASK') . "</font>
			<br />" . JText::_('MOD_STOOLPIGEON_MAINTAIN_SAME_ORDER')
					. "<font color='red'>[" . strtoupper($client) . "] " . $tlt . "." . $file_comparable . "</font>"
					. JText::_('MOD_STOOLPIGEON_HAVE_PENDING')
					. "<br />";
			}
			elseif (!isset($matrix_info['incidences']['key_non_existent_in_target'][$client][$file_comparable])
				&& !isset($matrix_info['incidences']['key_non_existent_in_source'][$client][$file_comparable])
				&& $without_sections_disturbing == '1')
			{
				$counter      = '0';
				$changes      = '0';
				$source_file  = '';
				$target_file  = '';
				$source_lines = array();
				$target_lines = array();

				$source_file_path = $source_path . "/" . $slt . "." . $file_comparable;
				$target_file_path = $target_path . "/" . $tlt . "." . $file_comparable;

				$header_lines_source = $matrix_info['file_existent'][$client
				. '_ini_files']['source']['files_info'][$file_comparable]['counter']['first_key'];

				$header_lines_target = $matrix_info['file_existent'][$client
				. '_ini_files']['target']['files_info'][$file_comparable]['counter']['first_key'];

				$source_file = @file_get_contents($source_file_path);
				$target_file = @file_get_contents($target_file_path);

				//replaced due seems this one can to fail with Mac or Windows EOL format
				//$source_lines = explode("\n", $source_file);
				$source_lines = preg_split('/\r\n|\r|\n/', $source_file);

				//replaced due seems this one can to fail with Mac or Windows EOL format
				//$target_lines = explode("\n", $target_file);
				$target_lines = preg_split('/\r\n|\r|\n/', $target_file);

				$target_keys = $matrix_info['file_existent'][$client . '_ini_files']['target']['files_info'][$file_comparable]['keys'];

				foreach ($source_lines as $source_line)
				{
					$counter++;
					$trimmed_source_line = trim($source_line);

					if ((empty($source_line)) || ($source_line{0} == '#') || ($source_line{0} == ';') || $trimmed_source_line{0} == '[')
					{
						if (($counter > $header_lines_source))
						{
							if (empty($source_line))
							{
								$source_line_number   = $counter - $header_lines_source;
								$relative_line_number = (($header_lines_target + $counter) - $header_lines_source) - 1;

								if (!empty($target_lines[$relative_line_number]))
								{
									if ($matrix_info['config']['experimental_options']
										['report_files_keys_addeds_or_deleteds'] == '1')
									{
										echo "<br />" . JText::_('MOD_STOOLPIGEON_EMPTY_LINE_ADDED')
											. $tlt . "." . $file_comparable
											. JText::_('MOD_STOOLPIGEON_AND_LINE')
											. ($relative_line_number + 1) . "<br />";
									}
									$target_lines[$relative_line_number] = "";
									$changes                             = '1';
								}

							}
							elseif ($source_line{0} == ';')
							{
								$source_line_number   = $counter - $header_lines_source;
								$relative_line_number = (($header_lines_target + $counter) - $header_lines_source) - 1;

								if (empty($target_lines[$relative_line_number]) || $target_lines[$relative_line_number]{0} != ';')
								{
									if ($matrix_info['config']['experimental_options']
										['report_files_keys_addeds_or_deleteds'] == '1')
									{
										echo "<br />" . JText::_('MOD_STOOLPIGEON_COMMENT_1_ADDED')
											. $tlt . "." . $file_comparable
											. JText::_('MOD_STOOLPIGEON_AND_LINE') . ($relative_line_number + 1) . "<br />";
									}
									$target_lines[$relative_line_number] = $source_line;
									$changes                             = '1';
								}
								elseif (empty($target_lines[$relative_line_number])
									|| $target_lines[$relative_line_number]{0} == ';')
								{
									$target_lines[$relative_line_number] = $source_line;
								}

							}
							elseif ($source_line{0} == '#')
							{
								$source_line_number   = $counter - $header_lines_source;
								$relative_line_number = (($header_lines_target + $counter) - $header_lines_source) - 1;

								if (empty($target_lines[$relative_line_number]) || $target_lines[$relative_line_number]{0} != '#')
								{
									if ($matrix_info['config']['experimental_options']
										['report_files_keys_addeds_or_deleteds'] == '1')
									{
										echo "<br />" . JText::_('MOD_STOOLPIGEON_COMMENT_2_ADDED')
											. $tlt . "." . $file_comparable
											. JText::_('MOD_STOOLPIGEON_AND_LINE') . ($relative_line_number + 1) . "<br />";
									}
									$target_lines[$relative_line_number] = $source_line;
									$changes                             = '1';
								}

							}
							elseif ($trimmed_source_line{0} == '[')
							{
								$source_line_number   = $counter - $header_lines_source;
								$relative_line_number = (($header_lines_target + $counter) - $header_lines_source) - 1;

								if (empty($target_lines[$relative_line_number]) || $target_lines[$relative_line_number]{0} != '[')
								{
									if ($matrix_info['config']['experimental_options']
										['report_files_keys_addeds_or_deleteds'] == '1')
									{
										echo "<br />" . JText::_('MOD_STOOLPIGEON_SECTION_ADDED')
											. $tlt . "." . $file_comparable
											. JText::_('MOD_STOOLPIGEON_AND_LINE') . ($relative_line_number + 1) . "<br />";
									}

									$target_lines[$relative_line_number] = $source_line;
									$changes                             = '1';
								}

							}
						}


					}
					elseif (strpos($source_line, '='))
					{

						list($source_key, $source_value) = explode('=', $source_line, 2);

						$source_line_number = $counter - $header_lines_source;

						$target_line_number = $matrix_info['file_existent'][$client
						. '_ini_files']['target']['files_info'][$file_comparable]['counter']['lines'][$source_key];

						$relative_line_number = ($header_lines_target + $source_line_number) - 1;

						if (($source_line_number) != ($target_line_number - $header_lines_target))
						{
							//unsorted key at target present.
							if ($matrix_info['config']['experimental_options']
								['report_files_keys_addeds_or_deleteds'] == '1')
							{
								echo "<br /><b>" . JText::_('MOD_STOOLPIGEON_FILE') . ":</b> " . $tlt . "." . $file_comparable
									. "<br /><b>"
									. JText::_('MOD_STOOLPIGEON_UNSORTED_KEY') . "</b>" . $source_key . "<br /><b>"
									. JText::_('MOD_STOOLPIGEON_IN_SOURCE_LINE') . "</b>"
									. ($source_line_number + $header_lines_source) . "<br />"
									. JText::_('MOD_STOOLPIGEON_FOUNDED_AT_TARGET_LINE') . $target_line_number
									. JText::_('MOD_STOOLPIGEON_MUST_BE_MOVED') . ($relative_line_number + 1) . "<br />";
								//matrix index start with value 0, then relative values must be decremented 1 time.
							}
							$target_lines[$relative_line_number] = $source_key . "=" . $matrix_info['file_existent'][$client
								. '_ini_files']['target']['files_info'][$file_comparable]['keys'][$source_key];
							$changes                             = '1';
						}
					}
					unset($source_line);
				}
				$content = '';

				if ($changes == '1' && !array_key_exists($file_comparable, $files_with_keys_to_keep_in_target))
				{
					$content              = implode("\n", $target_lines);
					$trimmedcontent       = trim($content);
					$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;
					JFile::write($new_target_file_path, $trimmedcontent);
					$files_to_zip .= $new_target_file_path . ",";
				}
				elseif ($changes == '1' && array_key_exists($file_comparable, $files_with_keys_to_keep_in_target))
				{
					$comment = '0';
					foreach ($files_with_keys_to_keep_in_target[$file_comparable] as $key_to_keep => $value_to_keep)
					{
						if (!in_array($key_to_keep . "=" . $value_to_keep, $target_lines))
						{
							if ($comment == '0')
							{
								$target_lines[] = ";Keys to keep in this language (speciffic commented keys than exists above, extra plurals, etc)";
								$comment        = '1';
							}

							$target_lines[] = $key_to_keep . "=" . $value_to_keep;
							//echo "SORTING: key to keep: " . $key_to_keep . " Text: " . $value_to_keep . "<br />";
						}
					}

					$content              = implode("\n", $target_lines);
					$trimmedcontent       = trim($content);
					$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;
					JFile::write($new_target_file_path, $trimmedcontent);
					$files_to_zip .= $new_target_file_path . ",";

				}
				elseif ($changes == '0' && array_key_exists($file_comparable, $files_with_keys_to_keep_in_target))
				{
					$comment = '0';

					foreach ($files_with_keys_to_keep_in_target[$file_comparable] as $key_to_keep => $value_to_keep)
					{
						if (!in_array($key_to_keep . "=" . $value_to_keep, $target_lines))
						{
							if ($comment == '0')
							{
								$target_lines[] = ";Keys to keep in this language (speciffic commented keys than exists above, extra plurals, etc)";
								$comment        = '1';
							}

							$target_lines[] = $key_to_keep . "=" . $value_to_keep;
							//echo "TO KEEP WITHOUT UNSORTED KEYS: key to keep: " . $key_to_keep . " Text: " . $value_to_keep . "<br />";
						}
					}

					$content              = implode("\n", $target_lines);
					$trimmedcontent       = trim($content);
					$new_target_file_path = $new_base_path . '/' . $client . '/' . $tlt . '.' . $file_comparable;
					JFile::write($new_target_file_path, $trimmedcontent);
					$files_to_zip .= $new_target_file_path . ",";

				}
			}
			unset($file_comparable);
		}
		if ($files_to_zip == '')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('MOD_STOOLPIGEON_WITHOUT_KEYS_TO_SORT') . $client);
		}
		else
		{

			$client_data['files_to_zip'] = $files_to_zip;
			create_files_pack($matrix_info, $client, $client_data);
		}

	}
}

function raw_output(&$matrix_info = array(), $client = '', $location = '')
{
	$slt        = $matrix_info['config']['source_language_tag'];
	$tlt        = $matrix_info['config']['target_language_tag'];
	$sv         = $matrix_info['config']['version_options']['source_version'];
	$tv         = $matrix_info['config']['version_options']['target_version'];
	$scape_html = $matrix_info['config']['experimental_extras']['scape_html'];

	if ($location == 'source')
	{
		$files_existent = get_files_existent($matrix_info, $client, $type_format = 'ini', $location = 'source', $required = 'content');

		if ($client == 'admin')
		{
			$path = $matrix_info['config']['paths']['saf'];

		}
		elseif ($client == 'site')
		{

			$path = $matrix_info['config']['paths']['ssf'];

		}
		elseif ($client == 'installation')
		{

			$path = $matrix_info['config']['paths']['sif'];
		}
		foreach ($files_existent as $file)
		{
			$file_content = '';
			$file_lines   = '';
			$file_name    = $slt . '.' . $file;
			$full_path    = $path . '/' . $file_name;
			$file_content = @file_get_contents($full_path);
			//replaced due seems this one can to fail with Mac or Windows EOL format
			//$file_lines = explode("\n", $file_content);
			$file_lines = preg_split('/\r\n|\r|\n/', $file_content);

			echo "<H1>" . strtoupper($client) . JText::_('MOD_STOOLPIGEON_SORT_DUMP')
				. JText::_('MOD_STOOLPIGEON_SOURCE')
				. JText::_('MOD_STOOLPIGEON_SORT_DUMP_FILE') . $file_name . "</H1><br />";

			foreach ($file_lines as $line)
			{
				$trimmed_line = trim($line);
				if ($scape_html == '1')
				{
					echo htmlspecialchars($line) . "<br />";
				}
				elseif ($scape_html == '0' && ((empty($line)) || ($line{0} == '#') || ($line{0} == ';') || ($trimmed_line{0} == '[')))
				{
					echo htmlspecialchars($line) . "<br />";
				}
				else
				{
					list($key, $value) = explode('=', $line, 2);
					echo htmlspecialchars($key) . "=" . $value . "<br />";
				}
				unset($line);
			}
			echo "<br />";
			unset($file);
		}
	}
	if ($location == 'target')
	{
		$files_existent = get_files_existent($matrix_info, $client, $type_format = 'ini', $location = 'target', $required = 'content');

		if ($client == 'admin')
		{
			$path = $matrix_info['config']['paths']['taf'];

		}
		elseif ($client == 'site')
		{

			$path = $matrix_info['config']['paths']['tsf'];

		}
		elseif ($client == 'installation')
		{

			$path = $matrix_info['config']['paths']['tif'];
		}
		foreach ($files_existent as $file)
		{
			$file_content = '';
			$file_lines   = '';
			$file_name    = $tlt . '.' . $file;
			$full_path    = $path . '/' . $file_name;
			$file_content = @file_get_contents($full_path);
			//replaced due seems this one can to fail with Mac or Windows EOL format
			//$file_lines = explode("\n", $file_content);
			$file_lines = preg_split('/\r\n|\r|\n/', $file_content);
			echo "<H1>" . strtoupper($client) . JText::_('MOD_STOOLPIGEON_SORT_DUMP')
				. JText::_('MOD_STOOLPIGEON_TARGET')
				. JText::_('MOD_STOOLPIGEON_SORT_DUMP_FILE') . $file_name . "</H1><br />";

			foreach ($file_lines as $line)
			{
				$trimmed_line = trim($line);
				if ($scape_html == '1')
				{
					echo htmlspecialchars($line) . "<br />";
				}
				elseif ($scape_html == '0' && ((empty($line)) || ($line{0} == '#') || ($line{0} == ';') || ($trimmed_line{0} == '[')))
				{
					echo htmlspecialchars($line) . "<br />";
				}
				else
				{
					list($key, $value) = explode('=', $line, 2);
					echo htmlspecialchars($key) . "=" . $value . "<br />";
				}
				unset($line);
			}
			echo "<br />";
			unset($file);
		}
	}
}

function isTag($language_tag = '')
{
	if (strpos($language_tag, "-") && strlen($language_tag) >= '5' && strlen($language_tag) <= '6')
	{
		list($left_side, $right_side) = explode('-', $language_tag, 2);
		if ((is_string($left_side) && is_string($right_side))
			&& (strtolower($left_side) == $left_side
				&& strtoupper($right_side) == $right_side))
		{
			if ((strlen($left_side) == '2' || strlen($left_side) == '3') && strlen($right_side) == '2')
			{
				return true;
			}
		}
	}

	return false;
}


function raw_output_single_quotes(&$matrix_info = array(), $client = '', $location = '')
{
	$slt                = $matrix_info['config']['source_language_tag'];
	$tlt                = $matrix_info['config']['target_language_tag'];
	$sv                 = $matrix_info['config']['version_options']['source_version'];
	$tv                 = $matrix_info['config']['version_options']['target_version'];
	$scape_html         = $matrix_info['config']['experimental_extras']['scape_html'];
	$only_special_cases = $matrix_info['config']['experimental_extras']['only_special_cases'];

	if ($location == 'source')
	{
		$files_existent = get_files_existent($matrix_info, $client, $type_format = 'ini', $location = 'source', $required = 'content');

		if ($client == 'admin')
		{
			$path = $matrix_info['config']['paths']['saf'];

		}
		elseif ($client == 'site')
		{

			$path = $matrix_info['config']['paths']['ssf'];

		}
		elseif ($client == 'installation')
		{
			$path = $matrix_info['config']['paths']['sif'];
		}
		$with_files_affected = '0';
		foreach ($files_existent as $file)
		{
			$file_content = '';
			$file_lines   = '';
			$file_name    = $slt . '.' . $file;
			$full_path    = $path . '/' . $file_name;
			$file_content = @file_get_contents($full_path);
			//replaced due seems this one can to fail with Mac or Windows EOL format
			//$file_lines = explode("\n", $file_content);
			$file_lines           = preg_split('/\r\n|\r|\n/', $file_content);
			$show_message_done    = '0';
			$file_have_detected   = '0';
			$special_case_present = '0';


			foreach ($file_lines as $line)
			{
				$trimmed_line = trim($line);

				if (empty($line) || $line{0} == '#' || $line{0} == ';' || $trimmed_line{0} == '[')
				{
					//echo htmlspecialchars($line) . "<br />";
				}
				else
				{
					list($key, $value) = explode('=', $line, 2);

					if ($only_special_cases == '0' && strpos($value, "'")
						|| ($only_special_cases == '1' && (strpos($value, "\\'") || strpos($value, "='")))
					)
					{
						$file_have_detected = '1';

						if ($file_have_detected == '1' && $show_message_done == '0')
						{
							echo "<H1>" . strtoupper($client) . JText::_('MOD_STOOLPIGEON_SORT_DUMP')
								. JText::_('MOD_STOOLPIGEON_SOURCE')
								. JText::_('MOD_STOOLPIGEON_SORT_DUMP_FILE') . $file_name . "</H1><br />";
							echo "<font color='red'>This one is not mandatory or rule, due single quotes are valid</font>,"
								. " but normally all the <u>single quotes NOT USED"
								. " for set or wrap values inside language strings</u> can to be replaced by him <b>"
								. htmlspecialchars('&#039;') . "</b> encoded value to avoid wrong results due few times keys can to be called
						 from JS code without 'JText escaping single quotes there' when is required"
								. " (is a Joomla Code issue than forze to escape from language files the affected single quotes)."
								. " <b>You also have noticed detectable special cases when present.</b><br /><br />";
							$show_message_done   = '1';
							$with_files_affected = '1';

						}

						echo "<br /><b><font color='darkorange'>REPORT FOR KEY: " . htmlspecialchars($key) . "</b></font><br /><br />";


						if (strpos($value, "='"))
						{
							echo "<font color='red'>Noticed special case:<br /></font>This string have <b>equal symbol before single quotes</b>."
								. " If these after the equal symbol are used to set or wrap values and not for show sample syntax to use (info text),"
								. " it's NOT recommended replace them by " . htmlspecialchars('&#039;') . "<br /><br />";
							$special_case_present = '1';
						}

						if (strpos($value, "\\'"))
						{
							echo "<font color='red'>Noticed special case:<br /></font>This string have <b>escaped single quotes present</b>."
								. " If used as escaped single quote to avoid JS issues, you also can replace the escaped ones by the single quote "
								. htmlspecialchars('&#039;') . " enconded value.<br />"
								. "Commenting than you have required escape the single quotes to the Global coordinator, "
								. "can help to solve the issue from the Joomla Code.<br /><br />";
							$special_case_present = '1';
						}

						if ($only_special_cases == '1' && $special_case_present == '1')
						{
							echo "<b>" . htmlspecialchars($key) . "</b>=" . htmlspecialchars($value) . "<br /><br />";
						}
						elseif ($only_special_cases == '0')
						{
							echo "<b>" . htmlspecialchars($key) . "</b>=" . htmlspecialchars($value) . "<br /><br />";
						}

					}
				}

				unset($line);
			}
			unset($file);
		}
		if ($with_files_affected == '0')
		{
			echo "<h2>Without files using directly single quotes at the SOURCE client: " . strtoupper($client) . "</h2><br >";
		}
	}
	if ($location == 'target')
	{
		$files_existent = get_files_existent($matrix_info, $client, $type_format = 'ini', $location = 'target', $required = 'content');

		if ($client == 'admin')
		{
			$path = $matrix_info['config']['paths']['taf'];

		}
		elseif ($client == 'site')
		{

			$path = $matrix_info['config']['paths']['tsf'];

		}
		elseif ($client == 'installation')
		{

			$path = $matrix_info['config']['paths']['tif'];
		}

		$with_files_affected = '0';

		foreach ($files_existent as $file)
		{
			$file_content = '';
			$file_lines   = '';
			$file_name    = $tlt . '.' . $file;
			$full_path    = $path . '/' . $file_name;
			$file_content = @file_get_contents($full_path);
			//replaced due seems this one can to fail with Mac or Windows EOL format
			//$file_lines = explode("\n", $file_content);
			$file_lines           = preg_split('/\r\n|\r|\n/', $file_content);
			$show_message_done    = '0';
			$file_have_detected   = '0';
			$special_case_present = '0';


			foreach ($file_lines as $line)
			{
				$trimmed_line = trim($line);
				if (empty($line) || $line{0} == '#' || $line{0} == ';' || $trimmed_line{0} == '[')
				{
					//echo htmlspecialchars($line) . "<br />";
				}
				else
				{
					list($key, $value) = explode('=', $line, 2);

					if ($only_special_cases == '0' && strpos($value, "'")
						|| ($only_special_cases == '1' && (strpos($value, "\\'") || strpos($value, "='")))
					)
					{
						$file_have_detected = '1';

						if ($file_have_detected == '1' && $show_message_done == '0')
						{
							echo "<H1>" . strtoupper($client) . JText::_('MOD_STOOLPIGEON_SORT_DUMP')
								. JText::_('MOD_STOOLPIGEON_TARGET')
								. JText::_('MOD_STOOLPIGEON_SORT_DUMP_FILE') . $file_name . "</H1><br />";
							echo "<font color='red'>This one is not mandatory or rule, due single quotes are valid</font>,"
								. " but normally all the <u>single quotes NOT USED"
								. " for set or wrap values inside language strings</u> can to be replaced by him <b>"
								. htmlspecialchars('&#039;') . "</b> encoded value to avoid wrong results due few times keys can to be called
						 from JS code without 'JText escaping single quotes there' when is required"
								. " (is a Joomla Code issue than forze to escape from language files the affected single quotes)."
								. " <b>You also have noticed detectable special cases when present.</b><br /><br />";
							$show_message_done   = '1';
							$with_files_affected = '1';
						}

						echo "<br /><b><font color='darkorange'>REPORT FOR KEY: " . htmlspecialchars($key) . "</b></font><br /><br />";


						if (strpos($value, "='"))
						{
							echo "<font color='red'>Noticed special case:<br /></font>This string have <b>equal symbol before single quotes</b>."
								. " If these after the equal symbol are used to set or wrap values and not for show sample syntax to use (info text),"
								. " it's NOT recommended replace them by " . htmlspecialchars('&#039;') . "<br /><br />";
							$special_case_present = '1';
						}

						if (strpos($value, "\\'"))
						{
							echo "<font color='red'>Noticed special case:<br /></font>This string have <b>escaped single quotes present</b>."
								. " If used as escaped single quote to avoid JS issues, you also can replace the escaped ones by the single quote "
								. htmlspecialchars('&#039;') . " enconded value.<br />"
								. "Commenting than you have required escape the single quotes to the Global coordinator, "
								. "can help to solve the issue from the Joomla Code.<br /><br />";
							$special_case_present = '1';
						}


						if ($only_special_cases == '1' && $special_case_present == '1')
						{
							echo "<b>" . htmlspecialchars($key) . "</b>=" . htmlspecialchars($value) . "<br /><br />";
						}
						elseif ($only_special_cases == '0')
						{
							echo "<b>" . htmlspecialchars($key) . "</b>=" . htmlspecialchars($value) . "<br /><br />";
						}

					}
				}
				unset($line);
			}
			unset($file);
		}

		if ($with_files_affected == '0')
		{
			echo "<h2>Without files using directly single quotes at the TARGET client: " . strtoupper($client) . "</h2><br >";
		}
	}
}


/*
Paul's Simple Diff Algorithm v 0.1
(C) Paul Butler 2007 <http://www.paulbutler.org/>
May be used and distributed under the zlib/libpng license.
This code is intended for learning purposes; it was written with short
code taking priority over performance. It could be used in a practical
application, but there are a few ways it could be optimized.
Given two arrays, the function diff will return an array of the changes.
I won't describe the format of the array, but it will be obvious
if you use print_r() on the result of a diff on some test data.
htmlDiff is a wrapper for the diff command, it takes two strings and
returns the differences in HTML. The tags used are <ins> and <del>,
which can easily be styled with CSS.

Modiffied a bit by valc
*/


function diff($old, $new)
{
	$maxlen = 0;
	$ret    = '';
	$matrix = array();
	foreach ($old as $oindex => $ovalue)
	{
		$nkeys = array_keys($new, $ovalue);
		foreach ($nkeys as $nindex)
		{
			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
				$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
			if ($matrix[$oindex][$nindex] > $maxlen)
			{
				$maxlen = $matrix[$oindex][$nindex];
				$omax   = $oindex + 1 - $maxlen;
				$nmax   = $nindex + 1 - $maxlen;
			}
		}
	}

	if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));

	return array_merge(
		diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
		array_slice($new, $nmax, $maxlen),
		diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

function htmlDiff($old, $new)
{
	$ret  = '';
	$diff = diff(explode(' ', $old), explode(' ', $new));
	foreach ($diff as $k)
	{
		if (is_array($k))
		{
			$ret .= (!empty($k['d']) ? "<del>" . implode(' ', $k['d']) . "</del> " : '') .
				(!empty($k['i']) ? "<ins>" . implode(' ', $k['i']) . "</ins> " : '');
		}
		else
		{
			$ret .= $k . ' ';
		}
	}

	return $ret;
}
