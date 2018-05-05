<?php

  class exportBD{
    public $host = "";
    public $user = "";
    public $pass = "";
    public $name = "";
    public $tables = '*';

    // Constructor
    public function __construct($host,$user,$pass,$name, $tables='*'){
      $this->host = $host;
      $this->user = $user;
      $this->pass = $pass;
      $this->name = $name;
      $this->tables = $tables;
    }

    /* backup the db OR just a table */
    public function start(){
      $host = $this->host;
      $user = $this->user;
      $pass = $this->pass;
      $name = $this->name;
      $tables = $this->tables;


      $link = mysql_connect($host,$user,$pass);
      mysql_set_charset('utf8mb4', $link);
      mysql_select_db($name,$link);

      //get all of the tables
      if($tables === '*')
      {
          $tables = array();
          $result = mysql_query('SHOW TABLES');
          while($row = mysql_fetch_row($result))
          {
              $tables[] = $row[0];
          }
      }
      else
      {
          $tables = is_array($tables) ? $tables : explode(',',$tables);
      }

      //cycle through
      foreach($tables as $table)
      {
          $table = mysql_real_escape_string($table, $link);
          $result = mysql_query('SELECT * FROM '.$table);
          $num_fields = mysql_num_fields($result);

          $return.= 'DROP TABLE '.$table.';';
          $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
          $return.= "\n\n".$row2[1].";\n\n";

          while($row = mysql_fetch_row($result))
          {
              $return.= 'INSERT INTO '.$table.' VALUES(';
              for($j=0; $j < $num_fields; $j++)
              {
                  $row[$j] = mysql_real_escape_string($row[$j], $link);
                  if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                  if ($j < ($num_fields-1)) { $return.= ','; }
              }
              $return.= ");\n";
          }

          $return.="\n\n\n";
      }

      //save file
      $fileName = 'db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql';
      $handle = fopen($fileName,'w+');
      fwrite($handle,$return);
      fclose($handle);

      return $fileName;
    }

  }
