%define app_home /opt/storage_v16/webroot/
%define sys_user tvmer
%define sys_group tvmer


Name: tstorage-crop-picture
Version: 0.5.0
Release: 20130305
Vendor: TVMining Ltd
Summary: resize Image and download as file stream
License: BSD
Group: Applications
Requires: httpd,php53u,php53u-pecl-imagick
Source0: storage-crop-picture-0.5.0.tar.gz
BuildRoot:  %{_tmppath}/%{name}-%{version}-%{release}

%description
%prep
%setup  -q -n %{name}-%{version}
%build

%install
rm -rf %{buildroot}
install -d %{buildroot}%{app_home}
install -d %{buildroot}%{_sysconfdir}/httpd/conf.d
cp -rp * %{buildroot}%{app_home}
install -m 0644 -p ./storage_v16.conf.template  %{buildroot}%{_sysconfdir}/httpd/conf.d/storage_v16.conf

%clean

%post

mkdir -p /var/www/html
mkdir -p /opt/cache
chmod 777 /opt/cache
php %{app_home}storage_v16.php
service httpd restart
%preun
service httpd restart > /dev/null 2>&1
%postun

%files
%defattr(-,root,root,-)
%dir %{app_home}
%config(noreplace) %{_sysconfdir}/httpd/conf.d/storage_v16.conf
%{app_home}/

