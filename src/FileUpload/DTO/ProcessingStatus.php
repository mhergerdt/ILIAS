<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\FileUpload\DTO;

use ILIAS\FileUpload\ScalarTypeCheckAware;

/**
 * Class ProcessingStatus
 *
 * The class is used by the processors to give feedback to the
 * UploadService about the validity of the current processed file.
 * This class only purpose is to transport data.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
final class ProcessingStatus
{
    use ScalarTypeCheckAware;
    /**
     * Upload is ok
     */
    const OK = 1;
    /**
     * Upload got rejected by a processor
     */
    const REJECTED = 2;
    /**
     * Upload got denied by a processor, the upload will be removed immediately
     */
    const DENIED = 4;
    /**
     * @var int $code
     */
    private $code;
    /**
     * @var string $message
     */
    private $message;


    /**
     * ProcessingStatus constructor.
     *
     * @param int    $code   The code OK or REJECTED.
     * @param string $reason The message which should be set to make the rejection more
     *                       understandable for other developers.
     *
     * @throws \InvalidArgumentException Thrown if the given code is not OK or REJECTED. The
     *                                   exception can also be thrown if the given arguments are not
     *                                   of the correct type.
     * @since 5.3
     */
    public function __construct($code, $reason)
    {
        $this->intTypeCheck($code, 'code');
        $this->stringTypeCheck($reason, 'reason');

        if ($code !== self::OK && $code !== self::REJECTED && $code !== self::DENIED) {
            throw new \InvalidArgumentException('Invalid upload status code received. The code must be OK or REJECTED.');
        }

        $this->code = $code;
        $this->message = $reason;
    }


    /**
     * @return int
     * @since 5.3
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * @return string
     * @since 5.3
     */
    public function getMessage()
    {
        return $this->message;
    }
}
