#################################################################################
#										#
# COREN (a) nolar@numeri.net							#
#										#
#										#
# Для выполнения команд требуется шелл sh, а также ряд базовых команд (cp,	#
# rm, mv, install...). Сам makefile работает только в GNU make.			#
# Шелл, команды и GNU make входят в MSYS (http://www.mingw.org/msys.shtml).	#
#										#
#										#
# Конфигурационные параметры задаются сразу после основного блока комментариев.	#
# Если есть необходимость их настроить тут - то пожалуйста. Но лучше, конечно,	#
# использовать для этого make.conf, переменные окружения или командную строку.	#
# Просто чтобы понапрасну не менять исходный файл.				#
#										#
#										#
# Доступные цели:								#
#										#
# all										#
#	Функциональный синоним для build, а также цель по умолчанию		#
#	(то есть на случай, если в командной строке цель явно не указана).	#
#										#
# distfiles									#
#	Сканирует все файлы и каталоги проекта и записывает их в специальный	#
#	файл Makefile.distfiles. Позже этот файл используется для получения	#
#	списка дистрибутивных файлов (то есть файлов, входящих в дистрибутив).	#
#	Список дистрибутивных файлов нужен практически для любой операции,	#
#	поэтому ни одна другая общая цель не будет выполнена, пока не создан	#
#	или не существует Makefile.distfiles; при попытке выполнения таких	#
#	целей будет выдаваться сообщение, что нужно сгенерировать список.	#
#	Перед генерацией списка убедитесь, что каталоги хорошо почищены от	#
#	мусора и иных ненужных файлов и каталогов, потому что иначе эти		#
#	ненужные файлы и каталоги будут учтены как дистрибутивные. Выполнять	#
#	эту цель имеет смысл только когда изменилась структура каталогов или	#
#	появились новые файлы в проекте.					#
#										#
# clean										#
#	Очищает все каталоги проекта от файлов, которые генерируются при	#
#	выполнении цели build. Никакие другие файлы, в том числе создаваемые	#
#	другими целями, либо созданные вручную, не трогаются.			#
#										#
# veryclean									#
#	Очищает все каталоги проекта от файлов, которые генерируются при	#
#	выполнении цели build, а также от всех файлов, которые подпадают под	#
#	определение мусорных (см. переменную trash): резервные копии и т.п.	#
#										#
# distclean									#
#	Очищает все каталоги проекта от файлов и папок, которые не помечены как	#
#	дистрибутивные; дистрибутивными считаются файлы и папки, перечисленные	#
#	в Makefile.distfiles (см. цель distfiles). Внимание! Папки, не		#
#	помеченные как дистрибутивные, стираются рекурсивно, даже если в них	#
#	есть файлы, помеченные как дистрибутивные. Поэтому при составлении	#
#	списка дистрибутивных файлов всегда включайте нужные папки, либо	#
#	просто используйте цель distfiles.					#
#										#
# build										#
#	Компилирует и компонует все файлы в этом каталоге, которые хоть как-то	#
#	можно компилировать и компоновать.					#
#										#
# install									#
#	Устанавливает файлы в целевой каталог, предварительно скомпилировав и	#
#	скомпоновав их (см. цель build). Целевой каталог задается в переменной	#
#	installdir.								#
#										#
# tar/tgz/rar/zip/...								#
#	Создает архив из установленной копии проекта (см. цель install),	#
#	даже если в установленную копию были внесены изменения, которых нет в	#
#	исходном коде; например, конфиги. По этой причине цель install		#
#	предварительно не выполняется, и предполагается что инсталляция уже	#
#	была выполнена самостоятельно.						#
#										#
# upload									#
#	TODO:									#
#	По задумке, должен загрузить установленную копию на ftp-сервер.		#
#										#
# siteoff/siteon								#
#	TODO:									#
#	По задумке, должны выполнить отключение/включение сайта с выводом	#
#	специального уведомления о том, что сайт отключен (через mod_rewrite).	#
#										#
#################################################################################
#
# Команды работы с файлами, понятные шеллу (которые он может выполнить/вызвать).
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
# Каталог, в котором будут установлены итоговые файлы (см. цель install).
# Каталог, при необходимости, будет создан.
#
installdir ?= $(CURDIR)/dst/
#
# Каталог, в котором будут создаваться архивы из готовых установок (см. цель tar).
# Каталог, при необходимости, будет создан.
#
packagedir ?= $(CURDIR)/pak/
#
# Список масок для файлов, которые считаются мусорными (см. цель veryclean).
# Маски в формате, понимаемом функциями patsubst/filter. Символ "%" обозначает
# любую строку, и должен встречаться в маске только один раз.
#
trash = %- %~ %.bak
#
#################################################################################
#
# Определяем цель по умолчанию, которая будет выполнена, если при вызове make
# иная цель не была указана явно (через параметр). Это всегда all, и всегда
# как синоним для build.
# Правило доступно всегда, даже если не задан distfiles (см. Makefile.distfiles),
# хотя при этом правило build будет недоступно.
#
all: build
.PHONY: all
#
# Задаем способ компиляции phps-файлов методом урезания из них все комментариев
# и упаковки их в более компактную и менее читабельную форму.
# Правило доступно всегда, даже если не задан distfiles (см. Makefile.distfiles).
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
# Собираем имена всех файлов и каталогов в проекте, начиная от корня,
# и до уровня вложенности 10. Если требуется еще глубже - дописать по аналогии.
# Сбор имен организован так, что учитываются даже имена, начинающиеся с точки.
# Простым wildcard'ом они не учитываются; требуется специальная маска с точкой.
# При этом специальные имена "." и ".." отсеиваются сразу.
#
# Список всех файлов и каталогов используется напрямую или опосредованно во
# многих командах в сочетании со списком distfiles или без него, поэтому его
# генерация важна и находится в безусловной секции.
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
# Если файл Makefile.distfiles существует, то мы его считываем, и упорядочиваем
# считанный из него список дистрибутивных файлов.
#
ifneq (,$(wildcard Makefile.distfiles))
include Makefile.distfiles
override distfiles := $(sort $(distfiles))
else
override distfiles :=
endif
#
# Если файл Makefile.distfiles не существует, либо в нем нет нужного списка
# дистрибутивных файлов, то любую цель мы определяем как ошибочную, и выводим
# соответствующее сообщение. Всю оставшуюся часть этого Makefile'а выполняем
# если и только если список дистрибутивных файлов получен.
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
# Вычисление списков файлов разного типа и назначения.
#
# trashablefiles - все существующие файлы, сочтенные мусорными.
# outofdistfiles - все существующие файлы, сочтенные не-дистрибутивными.
#
# Для остальных переменных особая система:
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
# Специальная группа all - это сборище из всех других групп, расфасованное
# по purpose и type.
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
