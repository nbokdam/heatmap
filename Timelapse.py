import argparse
import datetime
import urllib.request
from subprocess import Popen, PIPE
import shutil
import os
import time
import signal


def valid_date(s):
    try:
        return datetime.datetime.strptime(s, '%Y-%m-%d')
    except ValueError:
        msg = "Not a valid date: '{0}'.".format(s)
        raise argparse.ArgumentTypeError(msg)


def valid_time(s):
    try:
        return datetime.datetime.strptime(s, '%H:%M:%S')
    except ValueError:
        msg = "Not a valid time: '{0}'.".format(s)
        raise argparse.ArgumentTypeError(msg)


parser = argparse.ArgumentParser(
    prog='Timelapse',
    description="Generate timelapse from heatmap.",
    formatter_class=argparse.ArgumentDefaultsHelpFormatter)

parser.add_argument('heatmapurl', help='ie.com/heatmap', )
parser.add_argument('startdate', type=valid_date, help='YYYY-MM-DD')
parser.add_argument('starttime', type=valid_time, nargs='?', default='00:00:00', help='HH:MM:SS')
parser.add_argument('enddate', type=valid_date, help='YYYY-MM-DD')
parser.add_argument('endtime', type=valid_time, nargs='?', default='23:59:59', help='HH:MM:SS')
parser.add_argument('-r', '--resolution', type=int, nargs='?', default='10', help='Real-time interval between frames in minutes')
parser.add_argument('output', type=str, nargs='?', default='out.mp4', help='Output MP4-file')
parser.add_argument('-f', '--ffmpeg', type=str, nargs='?', default='ffmpeg', help='Location of ffmpeg')

args = parser.parse_args()

startdate = datetime.datetime.strftime(args.startdate, '%Y-%m-%d')
starttime = datetime.datetime.strftime(args.starttime, '%H:%M:%S')
startdatetime = datetime.datetime.strptime(startdate + ' ' + starttime, '%Y-%m-%d %H:%M:%S')

enddate = datetime.datetime.strftime(args.enddate, '%Y-%m-%d')
endtime = datetime.datetime.strftime(args.endtime, '%H:%M:%S')
enddatetime = datetime.datetime.strptime(enddate + ' ' + endtime, '%Y-%m-%d %H:%M:%S')

print("Selected server: " + args.heatmapurl)
print("Start: ")
print(startdatetime)
print("End: ")
print(enddatetime)
print('\n')

resolution = datetime.timedelta(hours=0, minutes=args.resolution, seconds=0)
pointerdatetime = startdatetime
i = 0

command = [args.ffmpeg,
           '-y',  # (optional) overwrite output file if it exists
           '-f', 'image2pipe',
           '-r', '25',
           '-s', '1920x1080',  # size of one frame
           '-pix_fmt', 'yuvj420p',
           '-i', '-',  # The imput comes from a pipe
           '-an',  # Tells FFMPEG not to expect any audio
           '-c:v', 'libx264',
           args.output]

ffmpeg_pipe = Popen(command, stdin=PIPE, stdout=PIPE)
i = 0;

while pointerdatetime <= enddatetime :
    tries = 0
    while tries < 5 :
        try:
            local_filename, headers = urllib.request.urlretrieve(
                'http://' + args.heatmapurl + '/image.php?jpeg&time=' + datetime.datetime.strftime(pointerdatetime, '%Y-%m-%d') +
                '%20' + datetime.datetime.strftime(pointerdatetime, '%H:%M:%S'))
            f = open(local_filename, "rb")
            shutil.copyfileobj(f, ffmpeg_pipe.stdin)
            f.close()
            os.remove(local_filename)
            pointerdatetime += resolution
            i += 1
            print('.', end="", flush=True)
            break
        except Exception:
            tries += 1
            time.sleep(5*tries)
ffmpeg_pipe.stdout.flush()
exit()