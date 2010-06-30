<?php

# =============================================================================
# application/functions.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// Generate HTML pagination links
function pagination($cur_page_nr, $items, $link, $limit = 20)
{
	$page_count = ceil($items / $limit);

	if ($page_count == 1 || $cur_page_nr > $page_count)
		return;

	// Define some important variables
	$prev_nr = 0;
	$range = 3;
	$padding = array(0 => 3, 1 => 2, 2 => 1, 3 => 0);
	$html = array();

	// Calculate start- and endpoint
	$start = $cur_page_nr-$range < 1 ? 1 : $cur_page_nr-$range;
	$end = $cur_page_nr+$range > $page_count ? $page_count : $cur_page_nr+$range;

	// Calculate left and right padding
	$left_padding = $padding[$end - $cur_page_nr];
	$right_padding = $padding[$cur_page_nr - $start];

	// Add padding to the start- and endpoint
	$start = $start - $left_padding < 1 ? 1 : $start - $left_padding;
	$end = $right_padding + $end > $page_count ? $page_count : $right_padding + $end;

	$page_nrs = range($start, $end);

	// Add a first and last page if necessary
	if ($start != 1)
		array_unshift($page_nrs, 1);
	if ($end < $page_count)
		$page_nrs[] = $page_count;

	// Previous page link
	$html[] = $cur_page_nr > 1 ? '<a href="'.sprintf($link, ($cur_page_nr-1)).'">Previous</a>' : '<span>Previous</span>';

	// Shoop da loop
	foreach ($page_nrs as $i => $nr)
	{
		if ($prev_nr+1 < $nr)
			$html[] = '&hellip;';

		$html[] = '<a href="'.sprintf($link, $nr).'">'.($nr == $cur_page_nr ? '<strong>['.$nr.']</strong>' : $nr).'</a>';
		$prev_nr = $nr;
	}

	// Next page link
	$html[] = $cur_page_nr < $page_count ? '<a href="'.sprintf($link, $cur_page_nr+1).'">Next</a>' : '<span>Next</span>';

	return '<p class="pagination">'.implode('&nbsp;&nbsp;', $html).'</p>';
}

// Generate a TOC (Table of Contents) from a HTML document.
function generate_toc($html, $limit = 6)
{
	/* Filter out blockquotes and code blocks, because we don't want any headers
	   that are located in these blocks in the TOC */
	$f_html = preg_replace('#<(blockquote|pre)[^>]*>.*?</\1>#s', '', $html);

	// Look for all the headers
	preg_match_all('#<h([1-'.$limit.'])>([^<]+)</h[1-'.$limit.']>#', $f_html,
		$matches, PREG_SET_ORDER);

	// Gahter the necessary information
	$tree = $headers = array();
	$highest_header = 6;

	foreach ($matches as $index => $match)
	{
		$tree[] = array(
			'title' => $match[2],
			'depth' => $match[1]);

		$headers[0][] = '<h'.$match[1].'>'.$match[2];
		$headers[1][] = '<h'.$match[1].' id="h-'.$index.'">'.$match[2];

		if ($match[1] < $highest_header)
			$highest_header = $match[1];
	}

	// Add IDs to the headers tags
	$html = str_replace($headers[0], $headers[1], $html);

	// Generate TOC tree/list
	// Originally from: http://stackoverflow.com/questions/901576/how-to-print-list-using-hierarchical-data-structure/901644#901644
	$depth = -1;
	$flag = false;
	$toc_html = '';

	foreach ($tree as $index => $row)
	{
		$row['depth'] = $row['depth'] - $highest_header;

		while ($row['depth'] > $depth)
		{
			$toc_html .= '<ul>'."\n".'<li>';
			$flag = false;
			$depth++;
		}

		while ($row['depth'] < $depth)
		{
			$toc_html .= '</li>'."\n".'</ul>'."\n";
			$depth--;
		}

		if ($flag)
		{
			$toc_html .= '</li>'."\n".'<li>';
			$flag = false;
		}

		$toc_html .= '<a href="#h-'.$index.'">'.$row['title'].'</a>';

		$flag = true;
	}

	while ($depth-- > -1)
		$toc_html .= '</li>'."\n".'</ul>'."\n";

	return $toc_html."\n".$html;
}
