use strict;
use warnings;
use lib qw(lib);
use File::Temp qw(tempdir);
use IO::File;
use English qw(-no_match_vars);
use Test::More tests => 6;

{
  use_ok('minoTour::Config');
}

{
  eval {
    my $cfg = minoTour::Config->new();
  };
  like($EVAL_ERROR, qr{Can't[ ]open}smix, q[Can't open file]);
}

{
  no warnings qw(redefine once);
  my $tmp = tempdir(CLEANUP => 1);
  my $cfg = "$tmp/mT_param.conf";
  my $io  = IO::File->new(qq[>$cfg]);
  print {$io} <<'EOT';
directory=/path/to/your/minotour/web/install/
memcache=127.0.0.1:11211
dbhost=127.0.0.1
dbuser=bob
dbpass=password1
EOT
  $io->close;
  local $minoTour::Config::CONFIG = $cfg;

  my $cfg = minoTour::Config->new();
  can_ok($cfg, qw(directory memcache dbhost dbuser dbpass phploc));
  is($cfg->dbuser, 'bob',       'dbuser accessor');
  is($cfg->dbpass, 'password1', 'dbpass accessor');
  is($cfg->phploc, q[],         'empty / unset');
}
