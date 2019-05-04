<?php
print_r($_POST['my_files']);
if(isset($_POST['my_files']))
{
    $error = ""; //error holder
    $post = $_POST; 
    $file_folder = "files/"; // folder to load files
    // Checking ZIP extension is available
    if(isset($post['my_files']) and count($post['my_files']) > 0)
    { 
        // Checking files are selected
        $zip = new ZipArchive(); // Load zip library 
        $zip_name1 = 'my_files_'.time().".zip"; // Zip name
        $zip_name = $_POST['path'].'/my-file/'.$zip_name1; // Zip name
        if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
        { 
         // Opening zip file to load files
        $error .= "* Sorry ZIP creation failed at this time";
        }else $error = 'else';
        foreach($post['my_files'] as $file)
        { 
            //$zip->addFile($file); // Adding files into zip
            $new_filename = substr($file,strrpos($file,'/') + 1);
            $zip->addFile($file,$new_filename);
        }
        $zip->close();
        
        if(file_exists($zip_name))
        {
            // push to download the zip
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="'.$zip_name1.'"');
            readfile($zip_name);
            unlink($zip_name);
        }
     
    }
}
echo $error;