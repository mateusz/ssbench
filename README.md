## Overview ##

SSBench would like to become a SilverStripe deployment containing a database generator and a couple of plausibe workflows for benchmarking purposes. The idea is to be able to easily get a feeling for how much load our server can handle, and to detect problems early. We prefer to use generic tests so it's easy to compare results across different environments.

## Database generation ##

To generate a reasonable database the following SilverStripe specifcs need to be considered:

* Table inheritance
* Relationships
* Hierarchy objects (tree-like nesting)
* Versioned objects (history and stages)

For this mechanism to be flexible, we need to use a scaling factor, so we are able to generate databases of different sizes to suit any system.

One proposed application is auctioning platform - most people have a good understanding how these work, and it allows us to include a mix of variety of actions:

* Large queries (Auctions)
* Small queries (Bids)
* Complex objects (CarAuction extends VehicleAuction extends Auction)
* Relationships (Bidder -> Bid -> Auction -> Auctioner, tree structure of categories)

Given the scaling factor N, the basic scale modifier is 2^N, with N>=4 (so the divisions by 16 work well):

* 2^N auctions
* 2^N/2 members
* 1/2 VehicleAuctions, of which 1/2 CarAuctions
* 1/8 auctions with N*2 bids, N comments
* 6/8 auctions with N/2 bids, N/4 comments
* 1/8 dead auctions (no bidders)
* 8 categories, N/4 levels down, each level multiplies by 4.

N=10 (MB sized database)
* 1024 auctions
* 512 members
* 512 VehicleAuctions, 256 CarAuctions
* 128 auctions with 20 bids, 10 comments
* 768 auctions with 5 bids, 2 comments
* 128 dead auctions
* 8 categories, 2 levels down, 128 subcategories

N=20 (GB sized database)
* 1048576 auctions
* 524288 members
* 524288 VehicleAuctions, 262144 CarAuctions
* 131072 auctions with 40 bids, 20 comments
* 786432 auctions with 10 bids, 5 comments
* 131072 dead auctions
* 8 categories, 5 levels down, 8192 subcategories

## Test considerations ##

The following factors will impact the results, but we won't have control over them - effectively this are the things we will be testing:

* Database configuration (PostgreSQL is assumed right now)
* Opcode cache
* Webserver
* Proxies/other caches
* Hardware

Some things we can control either via the database generation, or SilverStripe code:

* Database size (scale)
* Partial caching
* Template variable caching
* DataObject caches
* Type of workload

The following workloads come to mind:

* Running controller actions, which use ORM directly. This would imitate AJAX calls submitted by a web application frontend. Some combination of:
  * Read vs. write
  * Random (hitting different pages) vs. sequential (mostly hitting the same area)
  * Indexed vs. non-indexed columns
  * Across multiple inherited tables vs. just root table
  * Large set (100 or more rows) vs. small (1 row)
  * One operation vs. many per request
  * Traversing zero vs. one or more relationships
* More typical SilverStripe website workloads:
  * Rendering templates using a combination of the Layout, includes, loops, controls and recursion
  * Rendering similiar template with partial caching
  * Submitting a form and processing the data
  * Above, but with background file serving

## Cheatsheet for Linux monitoring

Based on Debian. Some of the commands listed below may not be available on a default system.

### Hardware info

* lshw
* hdparm
* lspci -v
* dmidecode
* dmesg | grep EXT3 - for checking ext3 journalling mode

### Hardware test

* pg_test_fsync - HD writes/s - can be useful for checking if there is a write-back cache on the way to the hardware.
* bonnie++ - HD bandwidth
* zcav - part of bonnie++, tests throughput of different areas of drive
* stream - Memory bandwidth, https://github.com/gregs1104/stream-scaling

### Hardware config

* hdparm -W 0 /dev/hda0 - disable HD write cache

### Software

* vmstat
* top
* apachetop
* pgtop
* http://kovyrin.net/2006/04/29/monitoring-nginx-with-rrdtool/ for a peek into nginx
