#!/usr/bin/perl
use strict;
use File::Basename;
use File::Spec;
use Cwd;

# Arguments
# 1 project name
# 2 Action - full or link
my $project_name = 'ifr';
my $install = 'link';

my ($volume,$iframe_folder,$file) = File::Spec->splitpath( File::Basename::dirname($0) );
$iframe_folder = Cwd::realpath($iframe_folder);
my $current_folder = Cwd::getcwd();
my $iframe_folder_relative = File::Spec->abs2rel($iframe_folder);


if($install eq 'link') {
#	`cp -R $iframe_folder/Scripts/installation $project_name/`;
# 	`cp -R $iframe_folder/css $project_name/css`;
# 	`cp -R $iframe_folder/js $project_name/js`;
# 	`cp -R $iframe_folder/templates $project_name/templates`;
#	`cd $iframe_folder`
#	`find -name ".svn" -exec rm -rf {} \;`
}

my %keywords = (
	"IFRAME_FOLDER_ABSOLUTE"=> $iframe_folder,
	"IFRAME_FOLDER_RELATIVE"=> $iframe_folder_relative, 
	"PROJECT_NAME"			=> $project_name
);
replaceInFile('index.php', %keywords);
replaceInFile('configuration.php', %keywords);

print "\n";

############################################## Functions #############################################
#Read the given file and replace all instance of the given keyword with the replacement.
sub replaceInFile {
	my $file = "$project_name/" . shift;
	my %keywords = @_;
	my $contents = getFileContents($file);
	
	while(my ($keyword, $replacement) = each(%keywords)) {
		my $replace = "%$keyword%";
		$contents =~ s/$replace/$replacement/g;
	}
	putFileContents($file, $contents);
}

#Read the file given as the argument and return the contents
sub getFileContents {
	my $file = shift;
	my @lines;
	unless(open (FILE,$file)) {
		die("Can't open '$file': $!");
	} else {
		@lines=<FILE>;
		close(FILE);
	}
	return join('',@lines);
}
#Write the data back to the file
sub putFileContents {
	my $file = shift;
	my $contents = shift;
	unless(open (OUT, ">$file")) {
		print("Can't open '$file' for writing: $!");
	} else {
		print OUT $contents;
	}
	close(OUT);
}