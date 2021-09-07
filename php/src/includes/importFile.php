<?php

   #Removes all comments from an HTML file
   function removeComments($S){
      $Output = "";
      while (strpos($S, "<!--") !== false){
         $Output .= substr($S, 0, strpos($S, "<!--"));
         $S = substr($S, strpos($S,"-->") + 3);
      }
      return $Output . $S;
   }

   #Gets substring from the end of $A to the beginning of $B
   function getFileContentsBetween($A,$Contents,$B){
      if (strpos($Contents,$A) !== false && strpos($Contents,$B) !== false){
         return substr($Contents,strpos($Contents,$A)+strlen($A),strpos($Contents,$B)-strlen($Contents));
      } else {
         return "ERROR";
      }
   }

   #Returns an array of values from the HTML code of a table
   function getArray($Table){
      $RS = '<tr bgcolor="';
      $RE = '</tr';
      $CS = '<td';
      $CE = '</td';

      $Output = [];

      #Do until there are no more rows
      while (strpos($Table,$RS) !== false){
         $r = [];
         $Row = getFileContentsBetween($RS,$Table,$RE);

         #Do until there are no more cells in the row
         while (strpos($Row,$CS) !== false){
               $string = getFileContentsBetween($CS,$Row,$CE);
               $string = substr($string,strpos($string,">")+1);
               array_push($r,$string);
               $Row = substr($Row,strpos($Row,$CE)+strlen($CE));
         }
         array_push($Output,$r);
         $Table = substr($Table,strpos($Table,$RE)+strlen($RE));
      }

      return $Output;
   }

   #Open file and get the contents and extention
   $TTFile = $_FILES['TTFile'];
   $FileRef = fopen($TTFile["tmp_name"],"r");
   $FileContents = fread($FileRef,$TTFile['size']);
   $Type = explode(".",$TTFile['name'])[1];

   require_once("dbvars.php");
   $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("failed to connect to database");

   #Check the file extension
   if ($Type=="html" || $Type=="txt"){
      $TableContents = getFileContentsBetween('<table class="sortable" border="0" cellspacing="0" cellpadding="4" id="sortable">',removeComments($FileContents),'</table>');
      $TTData = array_slice(getArray($TableContents),1);
   } else if ($Type=="csv"){

      #Handle .ttbl file
      $TTData = explode("\n",$FileContents);
      foreach($TTData as $i => $row){
         $TTData[$i] = explode(", ",$row);
      }

   } else {
      echo "Unsupported file type '" . $TTFile['type'] . "'";
   }
   fclose($FileRef);
   //echo "<pre>"; print_r($TTData); echo "</pre>";
   require("pushtodb.php");
   $importMsg = "Data imported successfully";
   $_SESSION['view'] = 'Master';
   unset($_SESSION['classList']);
?>
