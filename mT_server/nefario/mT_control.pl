#!/usr/bin/env perl
use DBI;
use strict;
use warnings;
use Cache::Memcached;
use Parallel::Loops;
use Getopt::Long;
use Data::Dumper;
use Readonly;
use English qw(-no_match_vars);
use Carp;
use lib qw(lib /opt/minotour/lib);
use minoTour::Config;

our $VERSION = q[0.1];
Readonly::Scalar our $SLEEPTIME => 10;

my $cfg = minoTour::Config->new; # Import variables from mT_param.conf

#Set global variables
my ($verbose, $development, $twitter);

GetOptions(verbose     => \$verbose,
	   development => \$development,
	   twitter     => \$twitter)   # flag
  or croak qq[Error in command line arguments\n];


#Set up a connection to memcache to upload data and process stuff
my $memd = Cache::Memcached->new(servers => [$cfg->memcache]);



#Set up a connection to the Gru database to monitor for active runs.

#my $dbh = DBI->connect('DBI:mysql:host=' . $dbhost . ';database=Gru',$dbuser,$dbpass,{ AutoCommit => 1, mysql_auto_reconnect=>1}) or die "Connection Error: $DBI::errstr\n";
my $dsn = sprintf q[DBI:mysql:host=%s;database=Gru], $cfg->dbhost;
my $dbh = DBI->connect($dsn, $cfg->dbuser, $cfg->dbpass, { AutoCommit => 1, mysql_auto_reconnect => 1});

#Define an array with a list of tasks that need to be completed for each database if the reference length is greater than 0
my @alignjobarray = qw(depthcoverage
		       percentcoverage
		       readlengthqual
		       readnumberlength
		       mappabletime);

#Define an array of jobs regardless of reflength
my @jobarray = qw(readnumber
		  maxlen
		  avelen
		  bases
		  histogram
		  histogrambases
		  reads_over_time2
		  average_time_over_time2
		  active_channels_over_time
		  readsperpore
		  average_length_over_time
		  lengthtimewindow
		  cumulativeyield
		  sequencingrate
		  ratiopassfail
		  ratio2dtemplate
		  readnumber2);#"whatsinmyminion2"

#Define an array of characters to print to the screen as a heartbeat...
my @heartbeat  = (q[.], q[!], q[#], q[!]);

#This is our master loop which will run endlessley checking for changes to the databases;
while (1) {
  $memd->set('perl_mem_cache_connection', 'We are fully operational.', $SLEEPTIME);

  # Build in a sleep time to stop the processor going mental on an
  # empty while loop... This number should be set fairly long on the
  # production verion...
  sleep $SLEEPTIME;
  if (!$verbose) {
    print $heartbeat[0] . "\r" or croak qq[Error printing: $ERRNO];
    push @heartbeat, shift @heartbeat;
  }

  #Run the twitter script to send background notifications
  if ($twitter) {
    my $command = sprintf q[%sphp %s/views/alertcheck_background.php], $cfg->phploc, $cfg->directory;
    system $command;
  }

  #Query the database to see if there are any active minION runs that need processing
  my $query = q[SELECT * FROM Gru.minIONruns where activeflag = 1];
  my $sth   = $dbh->prepare($query);
  $sth->execute;

  #Loop through results and if we have any, set a memcache variable containing a list of database names:
  my $run_counter = 0; # Set counter for number of active runs.
  while (my $ref = $sth->fetchrow_hashref) {
    $run_counter++;

    if ($verbose) {
      printf "%d\t%s\n", $run_counter, $ref->{runname} or croak qq[Error printing: $ERRNO];
    }

    my $runname = sprintf q[perl_active_%d], $run_counter;
    $memd->set($runname, $ref->{runname}, $SLEEPTIME);

    for my $job (@jobarray) {
      jobs($ref->{runname}, $job, $ref->{reflength}, $ref->{minup_version});
    }

    if ($ref->{reflength} > 0) {
      for my $alignjob (@alignjobarray) {
	jobs($ref->{runname}, $alignjob, $ref->{reflength}, $ref->{minup_version});
      }

      ##proc_align($ref->{runname},$dbh);
      my $aligncommand = sprintf q[perl mT_align.pl "%s" &], $ref->{runname};
      if ($verbose) {
	print $aligncommand . "\n" or croak qq[Error printing: $ERRNO];
      }
      system $aligncommand;
    }

    if ($verbose) {
      print "Executed...\n" or croak qq[Error printing: $ERRNO];
    }
  }

  #set the variable in memcached with an expiry of the same as the program is running.
  $memd->set('perl_proc_active', $run_counter, $SLEEPTIME);

  #check we have set the variable by getting it from memcahced

  my $num_active = $memd->get('perl_proc_active');

  #print "We have $num_active active runs retrieved from memcache\n";
  return 1;
}

exit;

#### We now define a series of sub routines which will be run to write
#### data to json and store it in memcache for access by the php
#### scripts on the server. These will run at three different
#### rates. Rapidly updating material will be written frequently
#### (every 10 seconds), intermediate datasets every 60 seconds and
#### complex analysis every 180 seconds. We will write a second set of
#### subroutines to manipulate data from table to table whilst still
#### keeping results available in memcache.

#As standard we pass variables as database_name,
sub jobs {
  my ($dbname, $jobname, $reflength, $minupversion) = @_;
  my $checkvar        = sprintf q[%s%s],       $dbname, $jobname;
  my $checkrunning    = sprintf q[%s%sstatus], $dbname, $jobname;
  my $checkingrunning = $memd->get($checkrunning);
  my $checking        = $memd->get($checkvar);

  if (!$checkingrunning) {
    if ($verbose) {
      print "replacing $checkvar\n" or croak qq[Error printing: $ERRNO];
    }

    #########
    # At the moment waits for script to complete before calculating
    # next - need to check if process still running and not execute
    # new version until it has finished...
    #
    my $command = sprintf q[%sphp mT_control_scripts.php dbname=%s jobname=%s reflength=%d prev=0 minupversion=%s &],
      $cfg->phploc, $dbname, $jobname, $reflength, $minupversion;

    system $command;

  } else {
    if ($verbose) {
      print "already running $checkvar\n" or croak qq[Error printing: $ERRNO];
    }
  }

  return 1;
}
