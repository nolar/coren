#################################################################################
#										#
# COREN (a) nolar@numeri.net							#
#										#
#										#
# ��� ���������� ������ ��������� ���� sh, � ����� ��� ������� ������ (cp,	#
# rm, mv, install...). ��� makefile �������� ������ � GNU make.			#
# ����, ������� � GNU make ������ � MSYS (http://www.mingw.org/msys.shtml).	#
#										#
#										#
# ���������������� ��������� �������� ����� ����� ��������� ����� ������������.	#
# ���� ���� ������������� �� ��������� ��� - �� ����������. �� �����, �������,	#
# ������������ ��� ����� make.conf, ���������� ��������� ��� ��������� ������.	#
# ������ ����� ���������� �� ������ �������� ����.				#
#										#
#										#
# ��������� ����:								#
#										#
# all										#
#	�������������� ������� ��� build, � ����� ���� �� ���������		#
#	(�� ���� �� ������, ���� � ��������� ������ ���� ���� �� �������).	#
#										#
# distfiles									#
#	��������� ��� ����� � �������� ������� � ���������� �� � �����������	#
#	���� Makefile.distfiles. ����� ���� ���� ������������ ��� ���������	#
#	������ �������������� ������ (�� ���� ������, �������� � �����������).	#
#	������ �������������� ������ ����� ����������� ��� ����� ��������,	#
#	������� �� ���� ������ ����� ���� �� ����� ���������, ���� �� ������	#
#	��� �� ���������� Makefile.distfiles; ��� ������� ���������� �����	#
#	����� ����� ���������� ���������, ��� ����� ������������� ������.	#
#	����� ���������� ������ ���������, ��� �������� ������ �������� ��	#
#	������ � ���� �������� ������ � ���������, ������ ��� ����� ���		#
#	�������� ����� � �������� ����� ������ ��� ��������������. ���������	#
#	��� ���� ����� ����� ������ ����� ���������� ��������� ��������� ���	#
#	��������� ����� ����� � �������.					#
#										#
# clean										#
#	������� ��� �������� ������� �� ������, ������� ������������ ���	#
#	���������� ���� build. ������� ������ �����, � ��� ����� �����������	#
#	������� ������, ���� ��������� �������, �� ���������.			#
#										#
# veryclean									#
#	������� ��� �������� ������� �� ������, ������� ������������ ���	#
#	���������� ���� build, � ����� �� ���� ������, ������� ��������� ���	#
#	����������� �������� (��. ���������� trash): ��������� ����� � �.�.	#
#										#
# distclean									#
#	������� ��� �������� ������� �� ������ � �����, ������� �� �������� ���	#
#	��������������; ��������������� ��������� ����� � �����, �������������	#
#	� Makefile.distfiles (��. ���� distfiles). ��������! �����, ��		#
#	���������� ��� ��������������, ��������� ����������, ���� ���� � ���	#
#	���� �����, ���������� ��� ��������������. ������� ��� �����������	#
#	������ �������������� ������ ������ ��������� ������ �����, ����	#
#	������ ����������� ���� distfiles.					#
#										#
# build										#
#	����������� � ��������� ��� ����� � ���� ��������, ������� ���� ���-��	#
#	����� ������������� � �����������.					#
#										#
# install									#
#	������������� ����� � ������� �������, �������������� ������������� �	#
#	����������� �� (��. ���� build). ������� ������� �������� � ����������	#
#	installdir.								#
#										#
# tar/tgz/rar/zip/...								#
#	������� ����� �� ������������� ����� ������� (��. ���� install),	#
#	���� ���� � ������������� ����� ���� ������� ���������, ������� ��� �	#
#	�������� ����; ��������, �������. �� ���� ������� ���� install		#
#	�������������� �� �����������, � �������������� ��� ����������� ���	#
#	���� ��������� ��������������.						#
#										#
# upload									#
#	TODO:									#
#	�� �������, ������ ��������� ������������� ����� �� ftp-������.		#
#										#
# siteoff/siteon								#
#	TODO:									#
#	�� �������, ������ ��������� ����������/��������� ����� � �������	#
#	������������ ����������� � ���, ��� ���� �������� (����� mod_rewrite).	#
#										#
#################################################################################
#
# ������� ������ � �������, �������� ����� (������� �� ����� ���������/�������).
#
SHELL		 = /bin/sh
ECHO		?= echo
RM		?= rm -f
CP		?= cp
MV		?= mv
CAT		?= cat
INSTALLDIR	?= install -d
INSTALLDOT	?= install -p
INSTALLPHP	?= install -p
TAR		?= tar
PHP		?= php
GREP		?= grep
#
# �������, � ������� ����� ����������� �������� ����� (��. ���� install).
# �������, ��� �������������, ����� ������.
#
installdir ?= $(CURDIR)/dst/
#
# �������, � ������� ����� ����������� ������ �� ������� ��������� (��. ���� tar).
# �������, ��� �������������, ����� ������.
#
packagedir ?= $(CURDIR)/pak/
#
# ������ ����� ��� ������, ������� ��������� ��������� (��. ���� veryclean).
# ����� � �������, ���������� ��������� patsubst/filter. ������ "%" ����������
# ����� ������, � ������ ����������� � ����� ������ ���� ���.
#
trash = %- %~ %.bak
#
#################################################################################
#
# ���������� ���� �� ���������, ������� ����� ���������, ���� ��� ������ make
# ���� ���� �� ���� ������� ���� (����� ��������). ��� ������ all, � ������
# ��� ������� ��� build.
# ������� �������� ������, ���� ���� �� ����� distfiles (��. Makefile.distfiles),
# ���� ��� ���� ������� build ����� ����������.
#
all: build
.PHONY: all
#
# ������ ������ ���������� phps-������ ������� �������� �� ��� ��� ������������
# � �������� �� � ����� ���������� � ����� ����������� �����.
# ������� �������� ������, ���� ���� �� ����� distfiles (��. Makefile.distfiles).
#
%.phtml : %.php
ifdef RELEASE
	$(CAT) $< | $(GREP) -v '/*rts*/' | $(PHP) -w > $@
else
	$(CAT) $< | $(PHP) -w > $@
endif
#
#################################################################################
#
# �������� ����� ���� ������ � ��������� � �������, ������� �� �����,
# � �� ������ ����������� 10. ���� ��������� ��� ������ - �������� �� ��������.
# ���� ���� ����������� ���, ��� ����������� ���� �����, ������������ � �����.
# ������� wildcard'�� ��� �� �����������; ��������� ����������� ����� � ������.
# ��� ���� ����������� ����� "." � ".." ����������� �����.
#
# ������ ���� ������ � ��������� ������������ �������� ��� ������������� ��
# ������ �������� � ��������� �� ������� distfiles ��� ��� ����, ������� ���
# ��������� ����� � ��������� � ����������� ������.
#
realfiles00 := ./
realfiles01 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles00))) $(addsuffix .*/,$(filter %/,$(realfiles00))) ))
realfiles02 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles01))) $(addsuffix .*/,$(filter %/,$(realfiles01))) ))
realfiles03 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles02))) $(addsuffix .*/,$(filter %/,$(realfiles02))) ))
realfiles04 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles03))) $(addsuffix .*/,$(filter %/,$(realfiles03))) ))
realfiles05 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles04))) $(addsuffix .*/,$(filter %/,$(realfiles04))) ))
realfiles06 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles05))) $(addsuffix .*/,$(filter %/,$(realfiles05))) ))
realfiles07 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles06))) $(addsuffix .*/,$(filter %/,$(realfiles06))) ))
realfiles08 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles07))) $(addsuffix .*/,$(filter %/,$(realfiles07))) ))
realfiles09 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles08))) $(addsuffix .*/,$(filter %/,$(realfiles08))) ))
realfiles10 := $(filter-out %/./ %/../,$(wildcard $(addsuffix */,$(filter %/,$(realfiles09))) $(addsuffix .*/,$(filter %/,$(realfiles09))) ))
realfiles := $(sort $(patsubst ./%,%,$(foreach index,01 02 03 04 05 06 07 08 09 10,$(realfiles$(index)))))
#
#################################################################################
#
distfiles:
	$(ECHO) "override distfiles = $(sort Makefile Makefile.distfiles $(realfiles))" > Makefile.distfiles
.PHONY: distfiles
#
#################################################################################
#
# ���� ���� Makefile.distfiles ����������, �� �� ��� ���������, � �������������
# ��������� �� ���� ������ �������������� ������.
#
ifneq (,$(wildcard Makefile.distfiles))
include Makefile.distfiles
override distfiles := $(sort $(distfiles))
else
override distfiles :=
endif
#
# ���� ���� Makefile.distfiles �� ����������, ���� � ��� ��� ������� ������
# �������������� ������, �� ����� ���� �� ���������� ��� ���������, � �������
# ��������������� ���������. ��� ���������� ����� ����� Makefile'� ���������
# ���� � ������ ���� ������ �������������� ������ �������.
#
ifeq (,$(distfiles))
%:
	@$(ECHO) "Sorry, but there is no list of distributed files. Can't do '$@'."
	@$(ECHO) "You have to repair 'Makefile.distfiles' either by calling 'make distfiles'"
	@$(ECHO) "(that target will work), or by retrieving that file from original archive."
else
#
#################################################################################
#
# ���������� ������� ������ ������� ���� � ����������.
#
# trashablefiles - ��� ������������ �����, ��������� ���������.
# outofdistfiles - ��� ������������ �����, ��������� ��-���������������.
#
# ��� ��������� ���������� ������ �������:
#
# <group><purpose><type>
# where	<group>   is a group name, typically by extension, but not forced to be so;
#	<purpose> is for how a file will be used:
#		mask      - for defining how this files must look like in distfiles,
#		sourceext - for setting extension of source files, if they have it
#		targetext - for setting extension of target files, if they have it,
#		source    - for source files, which are used to build targets,
#		target    - for target files, which are built from sources,
#		direct    - for files. which are copied without compilations;
#	<type>    is either list of files or their dirs.
#
# ����������� ������ all - ��� ������� �� ���� ������ �����, �������������
# �� purpose � type.
#
trashablefiles := $(filter     $(trash)    ,$(realfiles))
outofdistfiles := $(filter-out $(distfiles),$(realfiles))
#
phpmask        := %
phpsourceext   := .php
phptargetext   := .phtml
phpsourcefiles := $(filter $(phpsourceext) $(addsuffix $(phpsourceext),$(phpmask)),$(distfiles))
phptargetfiles := $(patsubst %$(phpsourceext),%$(phptargetext),$(phpsourcefiles))
phpdirectfiles := $(filter-out $(phptargetfiles),$(filter $(phptargetext) $(addsuffix $(phptargetext),$(phpmask)),$(distfiles)))
#
dotmask        := .htaccess %.htaccess 
dotsourceext   := .src
dottargetext   :=
dotsourcefiles := $(filter $(dotsourceext) $(addsuffix $(dotsourceext),$(dotmask)),$(distfiles))
dottargetfiles := $(patsubst %$(dotsourceext),%$(dottargetext),$(dotsourcefiles))
dotdirectfiles := $(filter-out $(dottargetfiles),$(filter $(dottargetext) $(addsuffix $(dottargetext),$(dotmask)),$(distfiles)))
#
allsourcefiles := $(phpsourcefiles) $(dotsourcefiles)
alltargetfiles := $(phptargetfiles) $(dottargetfiles)
alldirectfiles := $(phpdirectfiles) $(dotdirectfiles)
allsourcedirs  := $(sort $(dir $(allsourcefiles)))
alltargetdirs  := $(sort $(dir $(alltargetfiles)))
alldirectdirs  := $(sort $(dir $(alldirectfiles)))
#
#################################################################################
#
clean:
	-$(RM) -- $(alltargetfiles)
#
#################################################################################
#
veryclean:
	-$(RM) -- $(alltargetfiles) $(trashablefiles)
#
#################################################################################
#
distclean:
	-$(RM) -r -- $(outofdistfiles)
#
#################################################################################
#
build: $(alltargetfiles)
#
#################################################################################
#
install: build
	$(call install_macros_for_dir,$(sort $(alltargetdirs)  $(alldirectdirs) ))
	$(call install_macros_for_dot,$(sort $(dottargetfiles) $(dotdirectfiles)))
	$(call install_macros_for_php,$(sort $(phptargetfiles) $(phpdirectfiles)))
#
define install_macros_for_dir
	$(foreach item,$(1),$(INSTALLDIR) $(installdir)$(item)
	)
endef
#
define install_macros_for_dot
	$(foreach item,$(1),$(INSTALLDOT) $(item) $(installdir)$(item)
	)
endef
#
define install_macros_for_php
	$(foreach item,$(1),$(INSTALLPHP) $(item) $(installdir)$(item)
	)
endef
#
#################################################################################
#
# todo later
#
backup:
#
#################################################################################
#
tar:
	$(INSTALLDIR) $(packagedir)
	$(TAR) --create --file=- -C $(installdir) . > $(packagedir)coren.tar
tgz:
	$(INSTALLDIR) $(packagedir)
	$(TAR) --create --file=- -C $(installdir) . | gzip -9 > $(packagedir)coren.tgz
zip:
	$(INSTALLDIR) $(packagedir)
	cd $(installdir) && zip -R -9 -X -S -q $(packagedir)coren.zip \*
rar:
	$(INSTALLDIR) $(packagedir)
	cd $(installdir) && rar a -ds -y -inul -r -s -m5 $(packagedir)coren.rar \*
#
#################################################################################
#
upload:
siteon:
siteoff:
#
#################################################################################
#
.PHONY: clean veryclean distclean build install \
	tar tgz zip rar upload siteon siteoff
#
#################################################################################
#
endif
#
#################################################################################
# FIN.										#
#################################################################################
