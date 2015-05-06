// <?php
// function gz_uncompress($srcName, $dstName) {
// $string = implode("", gzfile($srcName));
// $fp = fopen($dstName, "w");
// fwrite($fp, $string, strlen($string));
// fclose($fp);
// } 

// function gz_compress($srcName, $dstName)
// {
// $fp = fopen($srcName, "r");
// $data = fread ($fp, filesize($srcName));
// fclose($fp);

// $zp = gzopen($dstName, "w9");
// gzwrite($zp, $data);
// gzclose($zp);
// }

// ?>

<?php
function gz_uncompress($src, $dst)
{
  if(file_exists($src))
  {
    $filesize = filesize($src);
    $src_handle = gzopen($src, "r9");
    if(!file_exists($dst))
    {
      $dst_handle = fopen($dst, "w");
      while(!feof($src_handle))
      {
	      $chunk = gzread($src_handle, 4096);
	      fwrite($dst_handle, $chunk);
      }
      gzclose($src_handle);
      fclose($dst_handle);
     }
    else
    {
	echo "dst does not exists";
    }
  }
  else
  {
    echo "src does not exists";
  }
}
function gz_compress($src, $dst)
{
  if(file_exists($src))
  {
    $filesize = filesize($src);
    $src_handle = fopen($src, "r");
    if(!file_exists($dst))
    {
      $dst_handle = gzopen($dst, "w9");
      while(!feof($src_handle))
      {
	      $chunk = fread($src_handle, 4096);
	      gzwrite($dst_handle, $chunk);
      }
      fclose($src_handle);
      gzclose($dst_handle);
    }
    else
    {
	echo "dst does not exists";
    }
  }
  
  else
  {
    echo "src does not exists";
  }
}
?>