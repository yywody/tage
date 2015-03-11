<?php
/**
 * User: 13leaf
 * Date: 15-3-7
 * Time: 上午4:40
 */
namespace Etag\Compiler;

use Etag\EtagException;

class TokenStream
{
    /*
     * @var Token[]
     */
    protected $tokens;

    protected $filename;

    protected $current;

    public function __construct($tokens,$filename)
    {
        $this->tokens=$tokens;
        $this->filename=$filename;
        $this->current=-1;
    }


    /**
     * 是否到达结尾
     * @return bool
     */
    public function isEOF()
    {
        return $this->test(Token::TYPE_EOF);
    }

    public function test($tokenType,$tokenValue=null)
    {
        if($this->tokens[$this->current+1]->type != $tokenType){
            return false;
        }
        if($tokenValue !== null && $this->tokens[$this->current+1]->value != $tokenValue){
            return false;
        }
        return true;
    }

    /**
     * 移动并返回下一个token
     * @return Token
     */
    public function next()
    {
        $this->current++;
        if($this->current >= count($this->tokens)){
            throw new EtagException('End template');
        }
        return $this->tokens[$this->current];
    }

    /**
     * 断言下一个Token并返回
     * @param $tokenType
     * @param null $tokenValue
     * @return Token
     */
    public function expect($tokenType,$tokenValue=null)
    {
        $token = $this->tokens[$this->current + 1];
        if($this->test($tokenType,$tokenValue)){
           return $this->next();
        }else{
            throw new CompileException($this->filename,sprintf('expect %s',$tokenValue==null?Token::typeToString($tokenType):($tokenValue.'['.Token::typeToString($tokenType).']')),$token->line,$token->col);
        }
    }

}