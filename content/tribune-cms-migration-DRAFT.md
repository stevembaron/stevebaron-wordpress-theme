---
title: "How We Migrated 40 TV Stations Off a Proprietary CMS in a Year"
slug: tribune-cms-migration
excerpt: "We took a 40-station national publishing platform off a custom Rails CMS, moved it onto WordPress VIP, and cut platform costs by about 80% in the process. Here's roughly how it went."
category: Product
tags: [Tribune, WordPress, CMS, migration, product]
status: draft
---

# How We Migrated 40 TV Stations Off a Proprietary CMS in a Year

Somewhere on a hard drive I no longer have access to, there's a slide I made in 2016 that opens with one sentence: *"the CMS is the single most expensive piece of software we own, and almost no one outside this room knows that."*

The pitch in that deck was that Tribune Media should stop running its proprietary Rails CMS for its 40-station local TV digital network and move the whole portfolio onto WordPress VIP. The eventual answer was yes. The path between yes and done took about a year. The savings were close to 80% of the platform line. The audience grew. The team retained. I want to write a little of this down because I get asked about it more than I expected to.

## What we were starting with

A custom Rails CMS that had been built inside the company over the course of a decade. Forty-plus station websites running on it. Custom integrations to every weather and news and ad vendor in the building. A small engineering team responsible for keeping the whole thing alive, plus a much larger ops team responsible for the editorial workflow on top of it.

The CMS worked. That's the important thing to acknowledge up front. It served roughly 100 million monthly uniques. It supported live video. It had a custom WYSIWYG. It was not, by any reasonable measure, broken software.

It was also, by any reasonable measure, the wrong software for the next five years. The team to maintain it was expensive. Every new vendor integration was a custom build. Every editorial feature request waited in a queue behind every operational fix. The engineers maintaining it were good engineers, but they were doing the work of three engineers each, and we couldn't hire fast enough to catch up.

We had also lost something subtle: control over our own roadmap. Every editorial improvement we wanted to make required platform engineering hours we didn't have. We were running a content business and most of our engineering capacity went into keeping the lights on for a content system, not into improving the content business.

## Why WordPress VIP

I knew when I started writing the proposal that WordPress would be a controversial answer inside the building. WordPress had, and still has, a reputation problem in big media — "the thing your daughter's blog runs on" — which is mostly unfair and is also mostly beside the point.

The case I made was a four-line case:

1. **The ops team will be more productive immediately.** Most journalists and producers either already know WordPress or learn it in an afternoon. Our custom CMS took a week of training and a permanent help desk.
2. **The vendor ecosystem is enormous and free.** Every weather widget, every ad partner, every analytics tool already has a WordPress integration that someone else maintains.
3. **WordPress VIP runs the platform, not us.** Scaling, security, uptime, edge caching, DDoS protection — all of that becomes someone else's job. Our small engineering team can focus on differentiating work.
4. **The cost line is dramatically lower.** Replacing the proprietary stack and its associated headcount with the VIP contract was roughly an 80% reduction in run cost, even accounting for the migration spend.

The fourth bullet was the bullet that closed the deal. The other three were what made it the right answer instead of just the cheap answer.

## The plan

A year, structured roughly like this:

- **Q1: One station, end-to-end.** Pick a station nobody loves. Cut over their site to WordPress VIP. Solve every integration problem on that one site. Document everything. Train one editorial team.
- **Q2: Five stations, in waves.** Reuse everything from Q1. Test all the load conditions. Solve the SEO redirect problem at scale.
- **Q3: Twenty stations.** Production at speed. Editorial training in batches.
- **Q4: The rest, plus the legacy decommission.** Including the conversations with vendors whose Rails integrations were now permanently obsolete.

The thing I am most proud of in retrospect is that we did not slip the schedule. We finished the quarter ahead of forecast on the run rate and shipped the last station three days early.

## What went well

A few things, mostly people-shaped:

- The team. The migration leads on both the engineering and the editorial-ops sides were people who had been at the company long enough to know where every body was buried. You cannot do this work with new hires. You need institutional memory.
- The decision to invest in one station completely before opening the next one. The Q1 station took twelve weeks. We spent another four weeks just polishing it. By the time we started Q2, we had a template that worked. Every later station took two to three weeks.
- The migration toolchain. We built a single migration script that took the proprietary CMS's content export, ran a transform pass, and wrote it directly into WordPress VIP. We tested the transform on three months of historical content per station before we cut over. Catastrophic edge cases were found in the test pass, not in production.
- The decision to write the editorial training as a self-paced video course rather than running in-person training for every station. The course was good. The stations took it in their own pace. The help desk volume on launch days was a quarter of what we expected.

## What I'd do differently

Three things, in roughly increasing order of regret:

**We under-invested in the editorial features that WordPress doesn't have out of the box.** We assumed that since WordPress can do almost everything, the gaps could wait. The gaps could not wait. Local TV stations have specific editorial workflows — breaking-news cut-ins, weather alert templates, election-night live blogs — that needed bespoke work, and we started that work later than we should have.

**We were slow to deprecate the proprietary CMS.** We kept it running in parallel for too long because it was the safety net. The safety net was expensive. Once a station migrated successfully, we should have moved on to retiring that station's old environment within thirty days. We waited closer to ninety, on average. That choice cost real money.

**We didn't tell the story externally well enough.** This was an 80% cost reduction on a major platform line, done in a year, on a national publishing network. It should have been a press story. We told it internally; we did not tell it externally. I think there was a missed opportunity to recruit talent into the team on the strength of the work itself.

## The thing I think about most

In the years since, I've watched a lot of media companies wrestle with this same decision. Almost all of them eventually move to a more modern, more open, more vendor-rich CMS. Almost all of them take longer than they thought they would. Almost all of them save a lot of money on the way.

The thing I wish someone had told me before we started: *the migration is the easy part.* The migration is mechanical. The hard part is the cultural shift inside the engineering and editorial teams from "we run our own everything" to "we participate in an ecosystem someone else runs." That mindset shift takes longer than the codebase shift, and it's the thing that determines whether you actually realize the value of the move.

If you're staring at a custom CMS and a flat cost line and a slow editorial workflow: it's almost always the right move. Start with one site, take it all the way to done before you open the next one, and tell the story when you finish.

— Steve
