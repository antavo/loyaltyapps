<?php
namespace Antavo\LoyaltyApps\Helper;

/**
 *
 */
class FileIterator implements \Iterator
{
    /**
     * @var string
     */
    protected $_filename;

    /**
     * @var resource
     */
    protected $_fh;

    /**
     * @var string
     */
    protected $_current;

    /**
     * @var int
     */
    protected $_position = 0;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->_filename = $filename;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @throws \RuntimeException
     */
    public function open()
    {
        if (!isset($this->_fh)) {
            if (!$this->_fh = fopen($this->_filename, 'r')) {
                throw new \RuntimeException(sprintf('Could not open file \'%s\'', $this->_filename));
            }
        }
    }

    /**
     *
     */
    public function close()
    {
        if (isset($this->_fh)) {
            fclose($this->_fh);
        }
    }

    /**
     * @return string
     */
    public function current()
    {
        return $this->_current;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     *
     */
    public function next()
    {
        $this->open();
        $this->_current = fgets($this->_fh);
        $this->_position++;
    }

    /**
     *
     */
    public function rewind()
    {
        if (isset($this->_fh)) {
            rewind($this->_fh);
            $this->_current = NULL;
            $this->_position = 0;
        }
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->_current !== FALSE;
    }
}
