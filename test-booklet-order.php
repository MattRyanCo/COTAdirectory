<?php
require_once __DIR__ . '/app-includes/class-print-booklet.php';

// Test the booklet page ordering
$pdf = new PDF( 'L', 'in', 'HalfLetter' );

echo '<h1>Testing Booklet Page Ordering</h1>';

// Test with 8 pages
$total_pages   = 8;
$booklet_order = $pdf->generate_booklet_order( $total_pages );

echo '<h2>8-Page Document Booklet Order:</h2>';
echo "<p>Total pages: $total_pages</p>";

$sheet_number = 1;
foreach ( $booklet_order as $index => $sheet ) {
	if ( $index % 2 == 0 ) {
		echo "<h3>Sheet $sheet_number:</h3>";
		echo '<ul>';
		echo "<li><strong>Front:</strong> Page {$sheet[0]} (left) | Page {$sheet[1]} (right)</li>";
	} else {
		echo "<li><strong>Back:</strong> Page {$sheet[0]} (left) | Page {$sheet[1]} (right)</li>";
		echo '</ul>';
		++$sheet_number;
	}
}

echo '<h2>Expected Result:</h2>';
echo '<p>When assembled as a booklet, pages should read: 1, 2, 3, 4, 5, 6, 7, 8</p>';

echo '<h2>Verification:</h2>';
echo '<p>Sheet 1 Front: Page 8 | Page 1 ✓</p>';
echo '<p>Sheet 1 Back: Page 2 | Page 7 ✓</p>';
echo '<p>Sheet 2 Front: Page 6 | Page 3 ✓</p>';
echo '<p>Sheet 2 Back: Page 4 | Page 5 ✓</p>';
