package minoTour::Config;
use strict;
use warnings;
use Carp;
use English qw(-no_match_vars);
use Carp;

our $CONFIG = q[mT_param.conf];
our $PARAMS;

BEGIN {
  $PARAMS = [qw(directory memcache dbhost dbuser dbpass phploc)];

  no strict 'refs';

  for my $p (@{$PARAMS}) {
    my $ns = "minoTour::Config::$p";

    *{$ns} = sub {
      my $self = shift;
      return $self->{$p};
    };
  }
}

sub new {
  my $class = shift;
  my $self  = {
	       map { $_ => q[] } @{$PARAMS}
	      };

  my $io = IO::File->new($CONFIG) or croak "Can't open $CONFIG for read: $ERRNO";
  while(<$io>) {
    chomp;
    my ($key, $value) = split /=/smx;

    if(!exists $self->{$key}) {
      carp qq[Warning: Skipping unsupported configuration parameter '$key'];
      next;
    }

    $self->{$key} = $value;
  }
  $io->close;

  bless $self, $class;
  return $self;
}

1;
