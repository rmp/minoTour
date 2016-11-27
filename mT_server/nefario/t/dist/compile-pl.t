# -*- mode: cperl; tab-width: 8; indent-tabs-mode: nil; basic-offset: 2 -*-
# vim:ts=8:sw=2:et:sta:sts=2
#########
# Author: rmp
#
package compile;
use strict;
use warnings;
use Test::More;
use English qw(-no_match_vars);
use Carp;

our $VERSION = q[2.44.1];

eval {
  require Test::Compile;
};

if($EVAL_ERROR) {
  plan skip_all => 'Test::Compile not installed';
} else {
  Test::Compile->import;
  my @files = ();

  for my $dir (qw(. bin)) {
    eval {
      opendir my $dh, $dir or croak qq[Error opening $dir];
      push @files, grep { `file $_` =~ /perl.*script/smix }
                   grep { -f }
                   map { "$dir/$_" } readdir $dh;
      closedir $dh or carp qq[Error closing $dir: $ERRNO];
    } or do {
      # nothing
    };
  }

  all_pl_files_ok(@files);
}

1;
