FROM catthehacker/ubuntu:act-latest

# Docker 28+ refuses to extract tarballs through symlinks, which breaks
# act's `docker cp` into /var/run/act. Replace the /var/run -> /run
# symlink with a real directory so act can copy into it.
RUN rm /var/run \
 && mkdir -p /var/run \
 && cp -a /run/. /var/run/
