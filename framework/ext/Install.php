<?php

/**
 * 数据库安装类
 */

namespace framework\ext;

class Install{

    /**
     * 获取SQL语句
     * @param  string $sql_path   SQL文件路径
     * @param  string $old_prefix 原始前缀
     * @param  string $new_prefix 新前缀
     * @param  string $separator  换行符
     * @return array
     */
    static public function mysql($sql_path,$old_prefix="",$new_prefix="",$separator=";\n") 
    {
        $commenter = array('#','--');
		  //判断文件是否存在
        if(!file_exists($sql_path))
            return false;
        
        $content = file_get_contents($sql_path);   //读取sql文件
        $content = str_replace(array($old_prefix, "\r"), array($new_prefix, "\n"), $content);//替换前缀
		
        //通过sql语法的语句分割符进行分割
        $segment = explode($separator,trim($content)); 

        //去掉注释和多余的空行
		$data=array();
        foreach($segment as  $statement)
        {
            $sentence = explode("\n",$statement);         
            $newStatement = array();
            foreach($sentence as $subSentence)
            {
                if('' != trim($subSentence))
                {
                    //判断是会否是注释
                    $isComment = false;
                    foreach($commenter as $comer)
                    {
                        if(preg_match("/^(".$comer.")/is",trim($subSentence)))
                        {
                            $isComment = true;
                            break;
                        }
                    }
                    //如果不是注释，则认为是sql语句
                    if(!$isComment)
                        $newStatement[] = $subSentence;                    
                }
            }           
     	    $data[] = $newStatement;		 	
        }

        //组合sql语句
        foreach($data as  $statement)
        {
            $newStmt = '';
            foreach($statement as $sentence)
            {
                $newStmt = $newStmt.trim($sentence)."\n";
            }    
			if(!empty($newStmt))            
          	{ 
				 $result[] = $newStmt;
			}
        }	
		return $result;
    }
}