---
title: "The Eval Problem"
slug: the-eval-problem
excerpt: "Changing a model is easy. Knowing whether you made it better is the actual job, and most teams haven't built the eval setup that question requires."
category: AI
tags: [AI, evals, RAG, product]
status: draft
---

# The Eval Problem

The single most consistent thing I see when I sit down with an AI team early in a project is this: they can ship a model change in an afternoon, but they can't tell you whether the change made things better.

That gap — between *we changed it* and *we know it's better* — is where most AI products quietly die.

## The "looks good to me" trap

Almost every new AI feature I get pulled into begins life in roughly the same way. A small team builds something, the demo is good, leadership funds it, and a year later the product has an organic users-love-it story sitting next to an unsolved we-can't-explain-why-it-degrades story.

Underneath, the workflow looks like this: someone changes a prompt, runs five queries through it, eyeballs the responses, says "yeah, that's better," and ships. The team I'm watching is rigorous about a hundred other things. They're not rigorous about this one because the alternative — a real eval setup — is significantly harder than it sounds.

## Why it's hard

A good eval setup needs four things, all of them annoying:

1. **A representative dataset.** Not the queries you wish your users were sending, the queries they're actually sending. Building this dataset is a labeling and cleaning project, not an engineering one, and it never finishes.
2. **An automated grader.** Either an LLM-as-judge, a programmatic rule set, or human raters — but something that scales past the team eyeballing fifty examples per change.
3. **Manual spot checks alongside the automated number.** Because the automated grader will be wrong in ways you don't see coming, and the cheapest signal that something's gone sideways is still "the model started saying something weird and a human noticed."
4. **A regression flag that fires loudly.** When a change improves your top-line eval score but tanks one of your four most important subcategories, you need that to land in the right person's inbox before the change ships, not after.

That's not infrastructure that a fast-moving early-stage team naturally builds. It looks like overhead. It feels like overhead. It is overhead. And then you ship a regression to all of your users and you spend three weeks rolling it back and you remember why it was worth it.

## What the better teams do

The pattern in the small handful of teams who handle this well is the same in every case: they treat evals as a product, not as a testing concern.

They have a person whose job is evals. They have a private eval set that lives separately from prompts and code, gets reviewed and updated weekly, and is treated as a piece of company IP. They have a dashboard. They have a Slack channel where the dashboard's red rows post themselves. They have a culture where saying "I'm not shipping this until the eval moves" is normal, not heroic.

The good news is none of this requires money or seniority. The team I worked with that had the best eval setup I've seen had four engineers and one person on a part-time contract who owned the dataset. The hardest part was getting everyone to take the dataset seriously. The actual code was a hundred lines.

## What to do next week

If you're an early-stage team without an eval setup, here's the cheapest possible version:

- Pick 50 queries from your real logs. Don't overthink the sample.
- For each query, write down what a great response would include.
- For each new prompt or model change, run those 50 queries and compare.
- Whoever's doing the change writes one paragraph explaining what's better and one paragraph explaining what's worse. That paragraph goes in the PR.

That's it. It's not a research-grade setup. It will catch most of the worst regressions and most of the most embarrassing degradations. It costs an hour of labeling per fifty examples. It will save you weeks downstream.

The product that's a year ahead of you in your category is doing some version of this. You don't need a more elaborate version — you need a started version.

— Steve
