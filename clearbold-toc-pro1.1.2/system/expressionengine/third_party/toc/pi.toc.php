<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'Table of Contents PRO',
  'pi_version' =>'1.1.2',
  'pi_author' =>'Mark J. Reeves / Clearbold, LLC',
  'pi_author_url' => 'http://www.clearbold.com/',
  'pi_description' => 'A plugin that parses HTML for heading tags (H3 or specified) and generates a table of contents with jump links.',
  'pi_usage' => toc::usage()
  );

class toc {

    public  $return_data = '';
    var $heading;
    var $ul_class;
    var $depth;

    /**
     * Constructor
     *
     *
     *
     * @access public
     * @return void
     */
    public function toc()
    {
        $this->EE =& get_instance();

        if ( trim($this->EE->TMPL->tagdata) == '')
            { return; }

        $this->heading = $this->EE->TMPL->fetch_param('heading', 'h3');
        $this->ul_class = $this->EE->TMPL->fetch_param('class', 'ul-toc');
        $this->depth = $this->EE->TMPL->fetch_param('depth', 1);

        $return_data = '<ul class="'.$this->ul_class.'">';

        $dom = new DOMDocument;
        $dom->loadHTML(utf8_decode(trim($this->EE->TMPL->tagdata)));
        $xpath = new DOMXPath($dom);
        //$nodes = $xpath->query('//*[self::'.$this->heading.']');
        $nodes = $xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');
        $i = 0;
        $start_heading = substr($this->heading,-1);
        $prev_heading = $start_heading;
        $last_heading = $start_heading;
        $real_level = 1;
        foreach( $nodes as $node )
        {
            $i++;
            if ($this->depth==1)
            {
                if ($node->nodeName==$this->heading)
                    $return_data .= "\n" . '<li class="level-' . $real_level . '"><a href="#heading' . $i . '">' . trim($node->nodeValue) . '</a></li>'; // port \n, trim to basic
            }
            else {
                $current_heading = substr($node->nodeName,-1);
                $real_level = $current_heading - $start_heading + 1;
                if ($current_heading >= $start_heading && $current_heading-$start_heading<$this->depth)
                {
                    if ($current_heading == $prev_heading && $i==1)
                    {
                        // do nothing
                    }
                    if ($current_heading > $prev_heading)
                        $return_data .= "\n<ul>";
                    if ($current_heading == $prev_heading && $i > 1)
                        $return_data .= "</li>";
                    if ($current_heading < $prev_heading)
                        $return_data .= "</li>\n</ul>\n</li>";

                    $return_data .= "\n" . '<li class="level-' . $real_level . '">';
                    $return_data .= '<a href="#heading' . $i . '">' . trim($node->nodeValue) . '</a>'; // port \n, trim to

                    $last_heading = $current_heading;
                    $prev_heading = $current_heading;
                }
            }
        }
        if ($this->depth>1)
        {
            for ($j=0; $j<=$last_heading-$start_heading; $j++)
            {
                $return_data .= "</li>\n</ul>\n";
            }
        }
        else
            $return_data .= "\n</ul>";

        // return
        $this->return_data = $return_data;
    }

    public function article()
    {

        if ( trim($this->EE->TMPL->tagdata) == '')
            { return; }

        $clean_html = strtr(trim($this->EE->TMPL->tagdata), array(
            '&quot;' => '&#34;',
            '&amp;' =>  '&#38;',
            '&apos;' => '&#39;',
            '&lt;' =>   '&#60;',
            '&gt;' =>   '&#62;',
            '&nbsp;' => '&#160;',
            '&copy;' => '&#169;',
            '&laquo;' => '&#171;',
            '&reg;' =>   '&#174;',
            '&raquo;' => '&#187;',
            '&trade;' => '&#8482;',
            '&rdquo;' => '&#8221;',
            '&ldquo;' => '&#8220;',
            '&rsquo;' => '&#8217;',
            '&lsquo;' => '&#8216;'
          ));

        $dom = new DOMDocument;
        //$dom->loadHTML(utf8_decode($this->EE->TMPL->tagdata));
        $dom->loadHTML($clean_html);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');
        $i = 0;
        foreach( $nodes as $node ) {
            $i++;
            $domAttribute = $dom->createAttribute('id');
            $domAttribute->value = 'heading' . $i;
            $node->appendChild($domAttribute);
        }

        $html_fragment = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));
        $return_data = $html_fragment;

        // return
        return $return_data;
    }

    // usage instructions
    public function usage()
    {
        ob_start();
?>
-------------------
HOW TO USE
-------------------
{exp:toc}{tag output containing HTML}{/exp:toc}

The {exp:toc} tag will output the table of contents as an unordered list of #links, with a class of "ul-toc".

{exp:toc:article}{tag output containing HTML}{/exp:toc:article}

The {exp:toc:article} tag will output the original content, with all headings updated with corresponding IDs.

The default heading parsed is H3. You can specify a heading tag of your choice as:

{exp:toc heading="h2"}{tag output containing HTML}{/exp:toc}
{exp:toc:article}{tag output containing HTML}{/exp:toc:article}

Note that in the Pro version, you do not need to specify the heading on the article tag.

Supported in Pro:

Use the class parameter to override the unordered list's class.

{exp:toc heading="h2" class="toc"}{tag output containing HTML}{/exp:toc}

Use the depth parameter to nest multiple levels of headings, starting with the specified heading.

{exp:toc heading="h2" class="toc" depth="2"}{tag output containing HTML}{/exp:toc}

This will produce a nested unordered list, containing H2 and H3 heading links.

    <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

/* End of file pi.toc.php */
/* Location: ./system/expressionengine/third_party/toc/pi.toc.php */