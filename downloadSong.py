#!/usr/bin/python

import pafy;
import sys

url = sys.argv[1];
video = pafy.new(url);
audiostreams = video.audiostreams;

pos = 0;
i=0;
maxSize = 10000000000000;


for a in audiostreams: 
	
	if a.get_filesize() < maxSize:
		pos = i;
		maxSize = a.get_filesize();
		i=i+1;
	if maxSize < 10000000:
	
		filename = audiostreams[pos].download(filepath="/home/pi/requests/",quiet=True);

print filename


