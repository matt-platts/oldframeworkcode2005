#!/usr/bin/perl

use Data::Dumper;

#/{=if ((?:!?\w+,?)+ ?=? ?[\w ]+)}(.*?){=end[ _]?if}/

$input = "Some input {=here} and more input {=over_here} - {=if this}got this {=end_if} also we have {=if that}got that{=end_if}";
%values = (
	this => "This value",	
);

my %matches = $input =~ /{=if ((?:!?\w+,?)+ ?=? ?[\w ]+)}(.*?){=end[ _]?if}/g;

foreach my $key (keys %matches){
	print "Value of  $key is: " . $matches{$key} . "\n";
	if ($values{$key}){
		print "We have a value for this key ($key)";	
	}
}

print Dumper %matches;

print "Done";
