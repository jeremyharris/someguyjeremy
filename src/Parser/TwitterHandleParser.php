<?php
namespace JeremyHarris\App\Parser;

use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\ContextInterface;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Element\Link;
/**
 * credit: http://commonmark.thephpleague.com/customization/inline-parsing/#example-1---twitter-handles
 */
class TwitterHandleParser extends AbstractInlineParser
{
    public function getCharacters() {
        return array('@');
    }

    public function parse(ContextInterface $context, InlineParserContext $inlineContext) {
        $cursor = $inlineContext->getCursor();

        // The @ symbol must not have any other characters immediately prior
        $previousChar = $cursor->peek(-1);
        if ($previousChar !== null && $previousChar !== ' ') {
            // peek() doesn't modify the cursor, so no need to restore state first
            return false;
        }

        // Save the cursor state in case we need to rewind and bail
        $previousState = $cursor->saveState();

        // Advance past the @ symbol to keep parsing simpler
        $cursor->advance();

        // Parse the handle
        $handle = $cursor->match('/^\w+/');
        if (empty($handle)) {
            // Regex failed to match; this isn't a valid Twitter handle
            $cursor->restoreState($previousState);

            return false;
        }

        $profileUrl = 'https://twitter.com/' . $handle;

        $inlineContext->getInlines()->add(new Link($profileUrl, '@'.$handle));

        return true;
    }
}

