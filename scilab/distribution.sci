
function [distribution] = shotDistribution(p_set, p_pick)
    distribution = zeros(p_set, 1);
    for i = 1:p_pick
        pick = ceil(rand()*p_set);
        distribution(pick) = distribution(pick) + 1;
    end
endfunction

function [shot, amount] = drawDistribution(p_distrib)
    amountShot = sum(p_distrib);
    amount = zeros(1, 1+max(p_distrib)-min(p_distrib));
    shot = (min(p_distrib):max(p_distrib))';
    for i = 0:max(p_distrib)-min(p_distrib)
        amount(i+1) = sum(p_distrib == i+min(p_distrib))/amountShot;
    end
    normValue = max(amount);
    for i = 0:max(p_distrib)-min(p_distrib)
        amount(i+1) = amount(i+1)/normValue;
    end
endfunction


//function [shot, amount] = drawDistribution(p_distrib)
//    amountShot = sum(p_distrib);
//    amount = zeros(1+max(p_distrib), 1);
//    shot = (0:max(p_distrib));
//    for i = 0:max(p_distrib)
//        amount(i+1) = sum(p_distrib == i)
//    end
//    amount = amount / sum(amount);
//endfunction

shots = 1000000;
out = zeros(100, 2);

figure(2);
clf();

for prop=7:2:7
    span = 2.84*(5*prop)^0.5
    distribution = shotDistribution(round(shots/prop), shots);
    [shot, amount] = drawDistribution(distribution);
    
    cumG = amount;
    s = size(shot)
    for i=1:s(1)
        cumG(1,i) = sum(amount(1:i))/sum(amount);
    end
    
    xgrid(1);
    plot(shot, cumG);
    //out(propSqrt, :) = [propSqrt sum(cumG > 0.01 & cumG < 0.99)];
end

//final = [(shot-min(shot))/span, cumG'];
//cumG = cumG';
