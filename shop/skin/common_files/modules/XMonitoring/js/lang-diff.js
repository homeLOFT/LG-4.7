/*
vim: set ts=2 sw=2 sts=2 et:
*/

/**
 * X-Monitoring scripts
 * 
 * @category   Modules
 * @package    X-Monitoring
 * @subpackage JS Library
 * @author     Michael Bugrov <mixon@x-cart.com> 
 * @version    e1db5cf03524aef7b3d94390d4b4baa6311fd42b, v2 (xcart_4_5_5), 2013-02-07 17:35:38, lang-diff.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 * 
 * @fileoverview
 * Registers a language handler for unified DIFF.
 *
 *
 * To use, include prettify.js and this file in your HTML page.
 * Then put your code in an HTML tag like
 *      <pre class="prettyprint lang-diff">(my DIFF code)</pre>
 *
 *
 * http://www.gnu.org/software/diffutils/manual/html_node/Detailed-Unified.html is the basis for the grammar.
 */
PR['registerLangHandler'](
    PR['createSimpleLexer'](
        [
            // A plain text
            [PR['PR_PLAIN'], /^[^\r\n]+/, null, ' \t\r\n']
        ],
        [
            // A hunk string
            [PR['PR_DECLARATION'], /^@@ \-\d{1,},\d{1,} \+\d{1,},\d{1,} @@/, null, '@@'],
            // Deleted string
            [PR['PR_SOURCE'], /^\-[^\r\n]*/, null, '-'],
            // Inserted string
            [PR['PR_STRING'], /^\+[^\r\n]*/, null, '+'],
        ]),
    ['diff', 'patch']);
