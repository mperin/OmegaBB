#!/usr/bin/perl     

open(FILE, $ARGV[0]) || die("Could not open $ARGV[0]"); 
while(<FILE> ) { 
   @temp_pair = split(/ \/\//, $_);
   @pair = split(/:/, @temp_pair[0]);
   $my_hash{@pair[0]} = @pair[1];
} 

open(FILE, "base.css") || die("Could not open base.css"); 
while(<FILE> ) { 
   while (($key,$value) = each(%my_hash)) {
      if ($_ =~ $key) {
	      while($_ =~ $key) {
             chomp($value);
             $_ =~ s/$key/$value/;
		  }
      }
   }
   print $_; 
}
