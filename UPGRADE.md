# Upgrade Guide

This page will document any changes that are needed between versions. We will only release breaking changes in major versions.

## v1 to v2

All control exports that use the airtable driver must now define a `uniqueIdColumnName` property. This should be the name of a column in the airtable being exported to that must be unique between records. As a rule of thumb, this should be the ID of the resource (user/group/role) being exported.

If this property is not defined, an exception will be thrown.
