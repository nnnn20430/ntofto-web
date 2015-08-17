all:: build
build:
	python ../blatter/blatter/__init__.py blat

deploy-ssh:
	ssh selenic 'cd /home/hg/www && hg pull -u'
deploy:
	cd /home/hg/www && hg pull -u
serve:
	python ../blatter/blatter/__init__.py serve
.PHONY: build deploy-ssh deploy serve
