<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace caoym\util;

use ReflectionClass;
use Doctrine\Common\Annotations\DocLexer;
use Doctrine\Common\Annotations\AnnotationException;
/**
 * A parser for docblock annotations.
 *
 * It is strongly discouraged to change the default annotation parsing process.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class DocParser
{
    /**
     * An array of all valid tokens for a class name.
     *
     * @var array
     */
    private static $classIdentifiers = array(
        DocLexer::T_IDENTIFIER,
        DocLexer::T_TRUE,
        DocLexer::T_FALSE,
        DocLexer::T_NULL
    );

    /**
     * The lexer.
     *
     * @var \Doctrine\Common\Annotations\DocLexer
     */
    private $lexer;

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names must be the raw names as used in the class, not the fully qualified
     * class names.
     *
     * @var array
     */
    private $ignoredAnnotationNames = array();

    /**
     * @var string
     */
    private $context = '';

    /**
     * Hash-map for handle types declaration.
     *
     * @var array
     */
    private static $typeMap = array(
        'float'     => 'double',
        'bool'      => 'boolean',
        // allow uppercase Boolean in honor of George Boole
        'Boolean'   => 'boolean',
        'int'       => 'integer',
    );

    /**
     * Constructs a new DocParser.
     */
    public function __construct()
    {
        $this->lexer = new DocLexer;
    }

    /**
     * Sets the annotation names that are ignored during the parsing process.
     *
     * The names are supposed to be the raw names as used in the class, not the
     * fully qualified class names.
     *
     * @param array $names
     *
     * @return void
     */
    public function setIgnoredAnnotationNames(array $names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    /**
     * Parses the given docblock string for annotations.
     *
     * @param string $input   The docblock string to parse.
     * @param string $context The parsing context.
     *
     * @return array Array of annotations. If no annotations are found, an empty array is returned.
     */
    public function parse($input, $context = '', $record_doc)
    {
        $pos = $this->findInitialTokenPosition($input);
        if ($pos === null) {
            return array();
        }

        $this->context = $context;

        $this->lexer->setInput(trim(substr($input, $pos), '* /'));
        $this->lexer->moveNext();

        return $this->Annotations($record_doc);
    }

    /**
     * Finds the first valid annotation
     *
     * @param string $input The docblock string to parse
     *
     * @return int|null
     */
    private function findInitialTokenPosition($input)
    {
        $pos = 0;

        // search for first valid annotation
        while (($pos = strpos($input, '@', $pos)) !== false) {
            // if the @ is preceded by a space or * it is valid
            if ($pos === 0 || $input[$pos - 1] === ' ' || $input[$pos - 1] === '*') {
                return $pos;
            }

            $pos++;
        }

        return null;
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param integer $token Type of token.
     *
     * @return boolean True if tokens match; false otherwise.
     */
    private function match($token)
    {
        if ( ! $this->lexer->isNextToken($token) ) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        return $this->lexer->moveNext();
    }

    /**
     * Attempts to match the current lookahead token with any of the given tokens.
     *
     * If any of them matches, this method updates the lookahead token; otherwise
     * a syntax error is raised.
     *
     * @param array $tokens
     *
     * @return boolean
     */
    private function matchAny(array $tokens)
    {
        if ( ! $this->lexer->isNextTokenAny($tokens)) {
            $this->syntaxError(implode(' or ', array_map(array($this->lexer, 'getLiteral'), $tokens)));
        }

        return $this->lexer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string     $expected Expected string.
     * @param array|null $token    Optional token.
     *
     * @return void
     *
     * @throws AnnotationException
     */
    private function syntaxError($expected, $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $message  = sprintf('Expected %s, got ', $expected);
        $message .= ($this->lexer->lookahead === null)
            ? 'end of string'
            : sprintf("'%s' at position %s", $token['value'], $token['position']);

        if (strlen($this->context)) {
            $message .= ' in ' . $this->context;
        }

        $message .= '.';

        throw AnnotationException::syntaxError($message);
    }

    /**
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     * @param $record_doc 记录文档
     * @return array
     */
    private function Annotations($record_doc = false)
    {
        $annotations = array();
        $doc_begin = 0;
        while (null !== $this->lexer->lookahead) {
            if (DocLexer::T_AT !== $this->lexer->lookahead['type']) {
                $this->lexer->moveNext();
                continue;
            }

            // make sure the @ is preceded by non-catchable pattern
            if (null !== $this->lexer->token && $this->lexer->lookahead['position'] === $this->lexer->token['position'] + strlen($this->lexer->token['value'])) {
                $this->lexer->moveNext();
                continue;
            }

            // make sure the @ is followed by either a namespace separator, or
            // an identifier token
            if ((null === $peek = $this->lexer->glimpse())
                || (DocLexer::T_NAMESPACE_SEPARATOR !== $peek['type'] && !in_array($peek['type'], self::$classIdentifiers, true))
                || $peek['position'] !== $this->lexer->lookahead['position'] + 1) {
                    $this->lexer->moveNext();
                    continue;
            }

            $doc_end = $this->lexer->lookahead['position'];
            $this->match(DocLexer::T_AT);
            $name = $this->Identifier();
            $value = $this->MethodCall();
            
            if($record_doc){
                 if(($count= count($annotations)) !==0){
                    $doc= $this->lexer->getInputUntilPosition($doc_end);
                    $text = substr($doc, $doc_begin);
                    $annotations[$count-1][1]['doc'] = $text;
                    $text = strstr($text, ')');
                    $annotations[$count-1][1]['desc'] = substr($text, 1);
                }
                $doc_begin = $doc_end;
            }
            $annotations[] = array(
                $name,
                $value
            );
        }
        if ($record_doc) {
            if (($count = count($annotations)) !== 0) {
                $doc = $this->lexer->getInputUntilPosition(0xFFFF);
                $text = substr($doc, $doc_begin);
                $annotations[$count-1][1]['doc'] = $text;
                $text = strstr($text, ')');
                $annotations[$count-1][1]['desc'] = substr($text, 1);
            }
        }
        return $annotations;
    }

    /**
     * MethodCall ::= ["(" [Values] ")"]
     *
     * @return array
     */
    private function MethodCall()
    {
        $values = array();

        if ( ! $this->lexer->isNextToken(DocLexer::T_OPEN_PARENTHESIS)) {
            return $values;
        }

        $this->match(DocLexer::T_OPEN_PARENTHESIS);

        if ( ! $this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
            $values = $this->Values();
        }

        $this->match(DocLexer::T_CLOSE_PARENTHESIS);

        return $values;
    }

    /**
     * Values ::= Array | Value {"," Value}* [","]
     *
     * @return array
     */
    private function Values()
    {
        $values = array($this->Value());

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->match(DocLexer::T_COMMA);

            if ($this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
                break;
            }

            $token = $this->lexer->lookahead;
            $value = $this->Value();

            if ( ! is_object($value) && ! is_array($value)) {
                $this->syntaxError('Value', $token);
            }

            $values[] = $value;
        }

        foreach ($values as $k => $value) {
            if (is_object($value) && $value instanceof \stdClass) {
                $values[$value->name] = $value->value;
            } else if ( ! isset($values['value'])){
                $values['value'] = $value;
            } else {
                if ( ! is_array($values['value'])) {
                    $values['value'] = array($values['value']);
                }

                $values['value'][] = $value;
            }

            unset($values[$k]);
        }

        return $values;
    }

    /**
     * Constant ::= integer | string | float | boolean
     *
     * @return mixed
     *
     * @throws AnnotationException
     */
    private function Constant()
    {
        $identifier = $this->Identifier();

        if ( ! defined($identifier) && false !== strpos($identifier, '::') && '\\' !== $identifier[0]) {
            list($className, $const) = explode('::', $identifier);

            $alias = (false === $pos = strpos($className, '\\')) ? $className : substr($className, 0, $pos);
            $found = false;

            switch (true) {
                case !empty ($this->namespaces):
                    foreach ($this->namespaces as $ns) {
                        if (class_exists($ns.'\\'.$className) || interface_exists($ns.'\\'.$className)) {
                             $className = $ns.'\\'.$className;
                             $found = true;
                             break;
                        }
                    }
                    break;

                case isset($this->imports[$loweredAlias = strtolower($alias)]):
                    $found     = true;
                    $className = (false !== $pos)
                        ? $this->imports[$loweredAlias] . substr($className, $pos)
                        : $this->imports[$loweredAlias];
                    break;

                default:
                    if(isset($this->imports['__NAMESPACE__'])) {
                        $ns = $this->imports['__NAMESPACE__'];

                        if (class_exists($ns.'\\'.$className) || interface_exists($ns.'\\'.$className)) {
                            $className = $ns.'\\'.$className;
                            $found = true;
                        }
                    }
                    break;
            }

            if ($found) {
                 $identifier = $className . '::' . $const;
            }
        }

        // checks if identifier ends with ::class, \strlen('::class') === 7
        $classPos = stripos($identifier, '::class');
        if ($classPos === strlen($identifier) - 7) {
            return substr($identifier, 0, $classPos);
        }

        if (!defined($identifier)) {
            throw AnnotationException::semanticalErrorConstants($identifier, $this->context);
        }

        return constant($identifier);
    }

    /**
     * Identifier ::= string
     *
     * @return string
     */
    private function Identifier()
    {
        // check if we have an annotation
        if ( ! $this->lexer->isNextTokenAny(self::$classIdentifiers)) {
            $this->syntaxError('namespace separator or identifier');
        }

        $this->lexer->moveNext();

        $className = $this->lexer->token['value'];

        while ($this->lexer->lookahead['position'] === ($this->lexer->token['position'] + strlen($this->lexer->token['value']))
                && $this->lexer->isNextToken(DocLexer::T_NAMESPACE_SEPARATOR)) {

            $this->match(DocLexer::T_NAMESPACE_SEPARATOR);
            $this->matchAny(self::$classIdentifiers);

            $className .= '\\' . $this->lexer->token['value'];
        }

        return $className;
    }

    /**
     * Value ::= PlainValue | FieldAssignment
     *
     * @return mixed
     */
    private function Value()
    {
        $peek = $this->lexer->glimpse();

        if (DocLexer::T_EQUALS === $peek['type']) {
            return $this->FieldAssignment();
        }

        return $this->PlainValue();
    }

    /**
     * PlainValue ::= integer | string | float | boolean | Array | Annotation
     *
     * @return mixed
     */
    private function PlainValue()
    {
        if ($this->lexer->isNextToken(DocLexer::T_OPEN_CURLY_BRACES)) {
            return $this->Arrayx();
        }

        if ($this->lexer->isNextToken(DocLexer::T_AT)) {
            return $this->Annotation();
        }

        if ($this->lexer->isNextToken(DocLexer::T_IDENTIFIER)) {
            return $this->Constant();
        }

        switch ($this->lexer->lookahead['type']) {
            case DocLexer::T_STRING:
                $this->match(DocLexer::T_STRING);
                return $this->lexer->token['value'];

            case DocLexer::T_INTEGER:
                $this->match(DocLexer::T_INTEGER);
                return (int)$this->lexer->token['value'];

            case DocLexer::T_FLOAT:
                $this->match(DocLexer::T_FLOAT);
                return (float)$this->lexer->token['value'];

            case DocLexer::T_TRUE:
                $this->match(DocLexer::T_TRUE);
                return true;

            case DocLexer::T_FALSE:
                $this->match(DocLexer::T_FALSE);
                return false;

            case DocLexer::T_NULL:
                $this->match(DocLexer::T_NULL);
                return null;

            default:
                $this->syntaxError('PlainValue');
        }
    }

    /**
     * FieldAssignment ::= FieldName "=" PlainValue
     * FieldName ::= identifier
     *
     * @return array
     */
    private function FieldAssignment()
    {
        $this->match(DocLexer::T_IDENTIFIER);
        $fieldName = $this->lexer->token['value'];

        $this->match(DocLexer::T_EQUALS);

        $item = new \stdClass();
        $item->name  = $fieldName;
        $item->value = $this->PlainValue();

        return $item;
    }

    /**
     * Array ::= "{" ArrayEntry {"," ArrayEntry}* [","] "}"
     *
     * @return array
     */
    private function Arrayx()
    {
        $array = $values = array();

        $this->match(DocLexer::T_OPEN_CURLY_BRACES);

        // If the array is empty, stop parsing and return.
        if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
            $this->match(DocLexer::T_CLOSE_CURLY_BRACES);

            return $array;
        }

        $values[] = $this->ArrayEntry();

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->match(DocLexer::T_COMMA);

            // optional trailing comma
            if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
                break;
            }

            $values[] = $this->ArrayEntry();
        }

        $this->match(DocLexer::T_CLOSE_CURLY_BRACES);

        foreach ($values as $value) {
            list ($key, $val) = $value;

            if ($key !== null) {
                $array[$key] = $val;
            } else {
                $array[] = $val;
            }
        }

        return $array;
    }

    /**
     * ArrayEntry ::= Value | KeyValuePair
     * KeyValuePair ::= Key ("=" | ":") PlainValue | Constant
     * Key ::= string | integer | Constant
     *
     * @return array
     */
    private function ArrayEntry()
    {
        $peek = $this->lexer->glimpse();

        if (DocLexer::T_EQUALS === $peek['type']
                || DocLexer::T_COLON === $peek['type']) {

            if ($this->lexer->isNextToken(DocLexer::T_IDENTIFIER)) {
                $key = $this->Constant();
            } else {
                $this->matchAny(array(DocLexer::T_INTEGER, DocLexer::T_STRING));
                $key = $this->lexer->token['value'];
            }

            $this->matchAny(array(DocLexer::T_EQUALS, DocLexer::T_COLON));

            return array($key, $this->PlainValue());
        }

        return array(null, $this->Value());
    }
}
