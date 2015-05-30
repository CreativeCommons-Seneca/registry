#!/usr/bin/perl

print hd($ARGV[0], $ARGV[1]) . "\n";

sub hd
{
     #String length is assumed to be equal
     my ($k,$l) = @_;
     my $len = length ($k);
     my $num_mismatch = 0;

     for (my $i=0; $i<$len; $i++)
     {
      ++$num_mismatch if substr($k, $i, 1) ne substr($l, $i, 1);
     }

     return $num_mismatch;
}
